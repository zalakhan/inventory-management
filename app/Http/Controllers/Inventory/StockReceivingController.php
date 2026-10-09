<?php

namespace App\Http\Controllers\Inventory;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\CancelStockReceivingRequest;
use App\Http\Requests\Inventory\StoreStockReceivingRequest;
use App\Http\Requests\Inventory\UpdateStockReceivingRequest;
use App\Models\Product;
use App\Models\StockReceiving;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\DocumentNumberService;
use App\Services\StockReceivingPostingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class StockReceivingController extends Controller
{
    public function __construct(
        private DocumentNumberService $documentNumberService,
        private StockReceivingPostingService $postingService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('stock_receiving.view');

        $receivings = StockReceiving::query()
            ->with(['supplier', 'warehouse', 'creator'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($q) use ($search) {
                    $q->where('receipt_number', 'like', "%{$search}%")
                        ->orWhere('supplier_reference', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('warehouse_id'), fn ($query) => $query->where('warehouse_id', $request->integer('warehouse_id')))
            ->when($request->filled('supplier_id'), fn ($query) => $query->where('supplier_id', $request->integer('supplier_id')))
            ->orderByDesc('receiving_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('inventory.stock_receiving.index', compact('receivings', 'warehouses', 'suppliers'));
    }

    public function create(): View
    {
        $this->authorize('stock_receiving.create');

        return view('inventory.stock_receiving.create', $this->formData());
    }

    public function store(StoreStockReceivingRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $receiptNumber = $this->documentNumberService->next(
            'stock_receiving.receipt_prefix',
            'stock_receiving.receipt_next',
        );

        $subtotal = $this->calculateSubtotal($validated['items']);

        $receiving = StockReceiving::create([
            'receipt_number' => $receiptNumber,
            'supplier_id' => $validated['supplier_id'],
            'warehouse_id' => $validated['warehouse_id'],
            'receiving_date' => $validated['receiving_date'],
            'supplier_reference' => $validated['supplier_reference'] ?? null,
            'subtotal' => $subtotal,
            'notes' => $validated['notes'] ?? null,
            'status' => 'draft',
            'created_by' => $request->user()->id,
        ]);

        $this->syncItems($receiving, $validated['items']);

        return redirect()
            ->route('inventory.stock_receiving.show', $receiving)
            ->with('success', 'Stock receiving draft created successfully.');
    }

    public function show(StockReceiving $stockReceiving): View
    {
        $this->authorize('stock_receiving.view');

        $stockReceiving->load(['items.product', 'supplier', 'warehouse', 'creator', 'poster', 'canceller']);

        return view('inventory.stock_receiving.show', [
            'receiving' => $stockReceiving,
        ]);
    }

    public function edit(StockReceiving $stockReceiving): View|RedirectResponse
    {
        $this->authorize('stock_receiving.edit');

        if ($stockReceiving->status !== 'draft') {
            return redirect()
                ->route('inventory.stock_receiving.show', $stockReceiving)
                ->with('error', 'Only draft receipts can be edited.');
        }

        $stockReceiving->load('items.product');

        return view('inventory.stock_receiving.edit', array_merge(
            $this->formData(),
            ['receiving' => $stockReceiving],
        ));
    }

    public function update(UpdateStockReceivingRequest $request, StockReceiving $stockReceiving): RedirectResponse
    {
        if ($stockReceiving->status !== 'draft') {
            return redirect()
                ->route('inventory.stock_receiving.show', $stockReceiving)
                ->with('error', 'Only draft receipts can be edited.');
        }

        $validated = $request->validated();

        $stockReceiving->update([
            'supplier_id' => $validated['supplier_id'],
            'warehouse_id' => $validated['warehouse_id'],
            'receiving_date' => $validated['receiving_date'],
            'supplier_reference' => $validated['supplier_reference'] ?? null,
            'subtotal' => $this->calculateSubtotal($validated['items']),
            'notes' => $validated['notes'] ?? null,
        ]);

        $stockReceiving->items()->delete();
        $this->syncItems($stockReceiving, $validated['items']);

        return redirect()
            ->route('inventory.stock_receiving.show', $stockReceiving)
            ->with('success', 'Stock receiving updated successfully.');
    }

    public function destroy(StockReceiving $stockReceiving): RedirectResponse
    {
        $this->authorize('stock_receiving.edit');

        if ($stockReceiving->status !== 'draft') {
            return back()->with('error', 'Only draft receipts can be deleted.');
        }

        $stockReceiving->items()->delete();
        $stockReceiving->delete();

        return redirect()
            ->route('inventory.stock_receiving.index')
            ->with('success', 'Draft receipt deleted successfully.');
    }

    public function post(StockReceiving $stockReceiving): RedirectResponse
    {
        $this->authorize('stock_receiving.post');

        try {
            $this->postingService->post($stockReceiving, auth()->user());
        } catch (InvalidArgumentException|InsufficientStockException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('inventory.stock_receiving.show', $stockReceiving)
            ->with('success', 'Stock receiving posted successfully.');
    }

    public function cancel(CancelStockReceivingRequest $request, StockReceiving $stockReceiving): RedirectResponse
    {
        try {
            $this->postingService->cancel(
                $stockReceiving,
                $request->user(),
                $request->validated('cancellation_reason'),
            );
        } catch (InvalidArgumentException|InsufficientStockException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('inventory.stock_receiving.show', $stockReceiving)
            ->with('success', 'Stock receiving cancelled successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'suppliers' => Supplier::where('is_active', true)->orderBy('name')->get(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'products' => Product::where('is_active', true)->orderBy('name')->get(['id', 'code', 'name', 'unit', 'purchase_rate']),
            'defaultWarehouseId' => auth()->user()->default_warehouse_id,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function syncItems(StockReceiving $receiving, array $items): void
    {
        foreach ($items as $item) {
            $lineTotal = bcmul((string) $item['quantity'], (string) $item['purchase_rate'], 2);

            $receiving->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'purchase_rate' => $item['purchase_rate'],
                'line_total' => $lineTotal,
            ]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function calculateSubtotal(array $items): string
    {
        $subtotal = '0';

        foreach ($items as $item) {
            $subtotal = bcadd($subtotal, bcmul((string) $item['quantity'], (string) $item['purchase_rate'], 2), 2);
        }

        return $subtotal;
    }
}
