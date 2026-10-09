<?php

namespace App\Http\Controllers\Sales;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\CancelSaleRequest;
use App\Http\Requests\Sales\StoreSaleRequest;
use App\Http\Requests\Sales\UpdateSaleRequest;
use App\Models\Area;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Salesman;
use App\Models\Warehouse;
use App\Services\DocumentNumberService;
use App\Services\SalePostingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class SaleController extends Controller
{
    public function __construct(
        private DocumentNumberService $documentNumberService,
        private SalePostingService $postingService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('sales.view');

        $sales = Sale::query()
            ->with(['customer', 'warehouse', 'salesman', 'creator'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('warehouse_id'), fn ($query) => $query->where('warehouse_id', $request->integer('warehouse_id')))
            ->when($request->filled('customer_id'), fn ($query) => $query->where('customer_id', $request->integer('customer_id')))
            ->orderByDesc('sale_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $customers = Customer::where('is_active', true)->orderBy('name')->get();

        return view('sales.invoices.index', compact('sales', 'warehouses', 'customers'));
    }

    public function create(): View
    {
        $this->authorize('sales.create');

        return view('sales.invoices.create', $this->formData());
    }

    public function store(StoreSaleRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $invoiceNumber = $this->documentNumberService->next(
            'sales.invoice_prefix',
            'sales.invoice_next',
        );

        $totals = $this->calculateTotals($validated['items'], $validated['discount_amount'] ?? 0);

        if (bccomp((string) ($validated['amount_paid'] ?? 0), $totals['total_amount'], 2) > 0) {
            return back()
                ->withInput()
                ->with('error', 'Amount paid cannot exceed invoice total.');
        }

        $sale = Sale::create([
            'invoice_number' => $invoiceNumber,
            'warehouse_id' => $validated['warehouse_id'],
            'customer_id' => $validated['customer_id'],
            'sale_date' => $validated['sale_date'],
            'salesman_id' => $validated['salesman_id'] ?? null,
            'area_id' => $validated['area_id'] ?? null,
            'subtotal' => $totals['subtotal'],
            'discount_amount' => $totals['discount_amount'],
            'total_amount' => $totals['total_amount'],
            'amount_paid' => $validated['amount_paid'] ?? 0,
            'outstanding_amount' => bcsub($totals['total_amount'], (string) ($validated['amount_paid'] ?? 0), 2),
            'notes' => $validated['notes'] ?? null,
            'status' => 'draft',
            'created_by' => $request->user()->id,
        ]);

        $this->syncItems($sale, $validated['items']);

        return redirect()
            ->route('sales.invoices.show', $sale)
            ->with('success', 'Sales invoice draft created successfully.');
    }

    public function show(Sale $invoice): View
    {
        $this->authorize('sales.view');

        $invoice->load(['items.product', 'customer', 'warehouse', 'salesman', 'area', 'creator', 'poster', 'canceller']);

        return view('sales.invoices.show', [
            'sale' => $invoice,
        ]);
    }

    public function edit(Sale $invoice): View|RedirectResponse
    {
        $this->authorize('sales.edit');

        if ($invoice->status !== 'draft') {
            return redirect()
                ->route('sales.invoices.show', $invoice)
                ->with('error', 'Only draft invoices can be edited.');
        }

        $invoice->load('items.product');

        return view('sales.invoices.edit', array_merge(
            $this->formData(),
            ['sale' => $invoice],
        ));
    }

    public function update(UpdateSaleRequest $request, Sale $invoice): RedirectResponse
    {
        if ($invoice->status !== 'draft') {
            return redirect()
                ->route('sales.invoices.show', $invoice)
                ->with('error', 'Only draft invoices can be edited.');
        }

        $validated = $request->validated();
        $totals = $this->calculateTotals($validated['items'], $validated['discount_amount'] ?? 0);

        if (bccomp((string) ($validated['amount_paid'] ?? 0), $totals['total_amount'], 2) > 0) {
            return back()
                ->withInput()
                ->with('error', 'Amount paid cannot exceed invoice total.');
        }

        $invoice->update([
            'warehouse_id' => $validated['warehouse_id'],
            'customer_id' => $validated['customer_id'],
            'sale_date' => $validated['sale_date'],
            'salesman_id' => $validated['salesman_id'] ?? null,
            'area_id' => $validated['area_id'] ?? null,
            'subtotal' => $totals['subtotal'],
            'discount_amount' => $totals['discount_amount'],
            'total_amount' => $totals['total_amount'],
            'amount_paid' => $validated['amount_paid'] ?? 0,
            'outstanding_amount' => bcsub($totals['total_amount'], (string) ($validated['amount_paid'] ?? 0), 2),
            'notes' => $validated['notes'] ?? null,
        ]);

        $invoice->items()->delete();
        $this->syncItems($invoice, $validated['items']);

        return redirect()
            ->route('sales.invoices.show', $invoice)
            ->with('success', 'Sales invoice updated successfully.');
    }

    public function destroy(Sale $invoice): RedirectResponse
    {
        $this->authorize('sales.edit');

        if ($invoice->status !== 'draft') {
            return back()->with('error', 'Only draft invoices can be deleted.');
        }

        $invoice->items()->delete();
        $invoice->delete();

        return redirect()
            ->route('sales.invoices.index')
            ->with('success', 'Draft invoice deleted successfully.');
    }

    public function post(Sale $invoice): RedirectResponse
    {
        $this->authorize('sales.post');

        try {
            $this->postingService->post($invoice, auth()->user());
        } catch (InvalidArgumentException|InsufficientStockException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('sales.invoices.show', $invoice)
            ->with('success', 'Sales invoice posted successfully.');
    }

    public function cancel(CancelSaleRequest $request, Sale $invoice): RedirectResponse
    {
        try {
            $this->postingService->cancel(
                $invoice,
                $request->user(),
                $request->validated('cancellation_reason'),
            );
        } catch (InvalidArgumentException|InsufficientStockException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('sales.invoices.show', $invoice)
            ->with('success', 'Sales invoice cancelled successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(['id', 'code', 'name', 'area_id']),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'salesmen' => Salesman::where('is_active', true)->orderBy('name')->get(['id', 'code', 'name', 'area_id']),
            'areas' => Area::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'products' => Product::where('is_active', true)->orderBy('name')->get(['id', 'code', 'name', 'unit', 'sale_rate']),
            'defaultWarehouseId' => auth()->user()->default_warehouse_id,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function syncItems(Sale $sale, array $items): void
    {
        foreach ($items as $item) {
            $gross = bcmul((string) $item['quantity'], (string) $item['rate'], 2);
            $lineDiscount = bcadd((string) ($item['discount_amount'] ?? 0), '0', 2);
            $lineTotal = bcsub($gross, $lineDiscount, 2);

            $sale->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'rate' => $item['rate'],
                'discount_amount' => $lineDiscount,
                'line_total' => $lineTotal,
            ]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array{subtotal: string, discount_amount: string, total_amount: string}
     */
    private function calculateTotals(array $items, string|float $invoiceDiscount): array
    {
        $subtotal = '0';

        foreach ($items as $item) {
            $gross = bcmul((string) $item['quantity'], (string) $item['rate'], 2);
            $lineDiscount = bcadd((string) ($item['discount_amount'] ?? 0), '0', 2);
            $subtotal = bcadd($subtotal, bcsub($gross, $lineDiscount, 2), 2);
        }

        $discountAmount = bcadd((string) $invoiceDiscount, '0', 2);
        $totalAmount = bcsub($subtotal, $discountAmount, 2);

        if (bccomp($totalAmount, '0', 2) < 0) {
            $totalAmount = '0';
        }

        return [
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
        ];
    }
}
