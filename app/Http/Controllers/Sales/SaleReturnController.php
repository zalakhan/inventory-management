<?php

namespace App\Http\Controllers\Sales;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\CancelSaleReturnRequest;
use App\Http\Requests\Sales\StoreSaleReturnRequest;
use App\Http\Requests\Sales\UpdateSaleReturnRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Warehouse;
use App\Services\DocumentNumberService;
use App\Services\SaleReturnPostingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class SaleReturnController extends Controller
{
    public function __construct(
        private DocumentNumberService $documentNumberService,
        private SaleReturnPostingService $postingService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('sale_returns.view');

        $returns = SaleReturn::query()
            ->with(['customer', 'warehouse', 'creator'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where('return_number', 'like', "%{$search}%");
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('warehouse_id'), fn ($query) => $query->where('warehouse_id', $request->integer('warehouse_id')))
            ->when($request->filled('customer_id'), fn ($query) => $query->where('customer_id', $request->integer('customer_id')))
            ->orderByDesc('return_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $customers = Customer::where('is_active', true)->orderBy('name')->get();

        return view('sales.returns.index', compact('returns', 'warehouses', 'customers'));
    }

    public function create(): View
    {
        $this->authorize('sale_returns.create');

        return view('sales.returns.create', $this->formData());
    }

    public function store(StoreSaleReturnRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $returnNumber = $this->documentNumberService->next(
            'sale_returns.return_prefix',
            'sale_returns.return_next',
        );

        $totals = $this->calculateTotals($validated['items']);

        $saleReturn = SaleReturn::create([
            'return_number' => $returnNumber,
            'warehouse_id' => $validated['warehouse_id'],
            'customer_id' => $validated['customer_id'],
            'sale_id' => $validated['sale_id'] ?? null,
            'return_date' => $validated['return_date'],
            'subtotal' => $totals['subtotal'],
            'total_amount' => $totals['total_amount'],
            'reason' => $validated['reason'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'draft',
            'created_by' => $request->user()->id,
        ]);

        $this->syncItems($saleReturn, $validated['items']);

        return redirect()
            ->route('sales.returns.show', $saleReturn)
            ->with('success', 'Sale return draft created successfully.');
    }

    public function show(SaleReturn $saleReturn): View
    {
        $this->authorize('sale_returns.view');

        $saleReturn->load(['items.product', 'customer', 'warehouse', 'sale', 'creator', 'poster', 'canceller']);

        return view('sales.returns.show', ['saleReturn' => $saleReturn]);
    }

    public function edit(SaleReturn $saleReturn): View|RedirectResponse
    {
        $this->authorize('sale_returns.create');

        if ($saleReturn->status !== 'draft') {
            return redirect()
                ->route('sales.returns.show', $saleReturn)
                ->with('error', 'Only draft returns can be edited.');
        }

        $saleReturn->load('items.product');

        return view('sales.returns.edit', array_merge(
            $this->formData(),
            ['saleReturn' => $saleReturn],
        ));
    }

    public function update(UpdateSaleReturnRequest $request, SaleReturn $saleReturn): RedirectResponse
    {
        if ($saleReturn->status !== 'draft') {
            return redirect()
                ->route('sales.returns.show', $saleReturn)
                ->with('error', 'Only draft returns can be edited.');
        }

        $validated = $request->validated();
        $totals = $this->calculateTotals($validated['items']);

        $saleReturn->update([
            'warehouse_id' => $validated['warehouse_id'],
            'customer_id' => $validated['customer_id'],
            'sale_id' => $validated['sale_id'] ?? null,
            'return_date' => $validated['return_date'],
            'subtotal' => $totals['subtotal'],
            'total_amount' => $totals['total_amount'],
            'reason' => $validated['reason'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        $saleReturn->items()->delete();
        $this->syncItems($saleReturn, $validated['items']);

        return redirect()
            ->route('sales.returns.show', $saleReturn)
            ->with('success', 'Sale return updated successfully.');
    }

    public function destroy(SaleReturn $saleReturn): RedirectResponse
    {
        $this->authorize('sale_returns.create');

        if ($saleReturn->status !== 'draft') {
            return back()->with('error', 'Only draft returns can be deleted.');
        }

        $saleReturn->items()->delete();
        $saleReturn->delete();

        return redirect()
            ->route('sales.returns.index')
            ->with('success', 'Draft return deleted successfully.');
    }

    public function post(SaleReturn $saleReturn): RedirectResponse
    {
        $this->authorize('sale_returns.post');

        try {
            $this->postingService->post($saleReturn, auth()->user());
        } catch (InvalidArgumentException|InsufficientStockException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('sales.returns.show', $saleReturn)
            ->with('success', 'Sale return posted successfully.');
    }

    public function cancel(CancelSaleReturnRequest $request, SaleReturn $saleReturn): RedirectResponse
    {
        try {
            $this->postingService->cancel(
                $saleReturn,
                $request->user(),
                $request->validated('cancellation_reason'),
            );
        } catch (InvalidArgumentException|InsufficientStockException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('sales.returns.show', $saleReturn)
            ->with('success', 'Sale return cancelled successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'products' => Product::where('is_active', true)->orderBy('name')->get(['id', 'code', 'name', 'unit', 'sale_rate']),
            'sales' => Sale::where('status', 'posted')->orderByDesc('sale_date')->limit(200)->get(['id', 'invoice_number', 'customer_id']),
            'defaultWarehouseId' => auth()->user()->default_warehouse_id,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function syncItems(SaleReturn $saleReturn, array $items): void
    {
        foreach ($items as $item) {
            $lineTotal = bcmul((string) $item['quantity'], (string) $item['rate'], 2);

            $saleReturn->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'rate' => $item['rate'],
                'line_total' => $lineTotal,
            ]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array{subtotal: string, total_amount: string}
     */
    private function calculateTotals(array $items): array
    {
        $subtotal = '0';

        foreach ($items as $item) {
            $subtotal = bcadd($subtotal, bcmul((string) $item['quantity'], (string) $item['rate'], 2), 2);
        }

        return [
            'subtotal' => $subtotal,
            'total_amount' => $subtotal,
        ];
    }
}
