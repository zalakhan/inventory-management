<?php

namespace App\Http\Controllers\Inventory;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\CancelStockAdjustmentRequest;
use App\Http\Requests\Inventory\StoreStockAdjustmentRequest;
use App\Http\Requests\Inventory\UpdateStockAdjustmentRequest;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\Warehouse;
use App\Services\DocumentNumberService;
use App\Services\StockAdjustmentPostingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class StockAdjustmentController extends Controller
{
    public function __construct(
        private DocumentNumberService $documentNumberService,
        private StockAdjustmentPostingService $postingService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('stock_adjustments.view');

        $adjustments = StockAdjustment::query()
            ->with(['warehouse', 'creator'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where('adjustment_number', 'like', "%{$search}%");
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('warehouse_id'), fn ($query) => $query->where('warehouse_id', $request->integer('warehouse_id')))
            ->when($request->filled('adjustment_type'), fn ($query) => $query->where('adjustment_type', $request->string('adjustment_type')))
            ->orderByDesc('adjustment_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('inventory.stock_adjustments.index', compact('adjustments', 'warehouses'));
    }

    public function create(): View
    {
        $this->authorize('stock_adjustments.create');

        return view('inventory.stock_adjustments.create', $this->formData());
    }

    public function store(StoreStockAdjustmentRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $adjustmentNumber = $this->documentNumberService->next(
            'stock_adjustments.adjustment_prefix',
            'stock_adjustments.adjustment_next',
        );

        $adjustment = StockAdjustment::create([
            'adjustment_number' => $adjustmentNumber,
            'warehouse_id' => $validated['warehouse_id'],
            'adjustment_date' => $validated['adjustment_date'],
            'adjustment_type' => $validated['adjustment_type'],
            'reason' => $validated['reason'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'draft',
            'created_by' => $request->user()->id,
        ]);

        $this->syncItems($adjustment, $validated['items']);

        return redirect()
            ->route('inventory.stock_adjustments.show', $adjustment)
            ->with('success', 'Stock adjustment draft created successfully.');
    }

    public function show(StockAdjustment $stockAdjustment): View
    {
        $this->authorize('stock_adjustments.view');

        $stockAdjustment->load(['items.product', 'warehouse', 'creator', 'poster', 'canceller']);

        return view('inventory.stock_adjustments.show', ['adjustment' => $stockAdjustment]);
    }

    public function edit(StockAdjustment $stockAdjustment): View|RedirectResponse
    {
        $this->authorize('stock_adjustments.create');

        if ($stockAdjustment->status !== 'draft') {
            return redirect()
                ->route('inventory.stock_adjustments.show', $stockAdjustment)
                ->with('error', 'Only draft adjustments can be edited.');
        }

        $stockAdjustment->load('items.product');

        return view('inventory.stock_adjustments.edit', array_merge(
            $this->formData(),
            ['adjustment' => $stockAdjustment],
        ));
    }

    public function update(UpdateStockAdjustmentRequest $request, StockAdjustment $stockAdjustment): RedirectResponse
    {
        if ($stockAdjustment->status !== 'draft') {
            return redirect()
                ->route('inventory.stock_adjustments.show', $stockAdjustment)
                ->with('error', 'Only draft adjustments can be edited.');
        }

        $validated = $request->validated();

        $stockAdjustment->update([
            'warehouse_id' => $validated['warehouse_id'],
            'adjustment_date' => $validated['adjustment_date'],
            'adjustment_type' => $validated['adjustment_type'],
            'reason' => $validated['reason'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        $stockAdjustment->items()->delete();
        $this->syncItems($stockAdjustment, $validated['items']);

        return redirect()
            ->route('inventory.stock_adjustments.show', $stockAdjustment)
            ->with('success', 'Stock adjustment updated successfully.');
    }

    public function destroy(StockAdjustment $stockAdjustment): RedirectResponse
    {
        $this->authorize('stock_adjustments.create');

        if ($stockAdjustment->status !== 'draft') {
            return back()->with('error', 'Only draft adjustments can be deleted.');
        }

        $stockAdjustment->items()->delete();
        $stockAdjustment->delete();

        return redirect()
            ->route('inventory.stock_adjustments.index')
            ->with('success', 'Draft adjustment deleted successfully.');
    }

    public function post(StockAdjustment $stockAdjustment): RedirectResponse
    {
        $this->authorize('stock_adjustments.post');

        try {
            $this->postingService->post($stockAdjustment, auth()->user());
        } catch (InvalidArgumentException|InsufficientStockException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('inventory.stock_adjustments.show', $stockAdjustment)
            ->with('success', 'Stock adjustment posted successfully.');
    }

    public function cancel(CancelStockAdjustmentRequest $request, StockAdjustment $stockAdjustment): RedirectResponse
    {
        try {
            $this->postingService->cancel(
                $stockAdjustment,
                $request->user(),
                $request->validated('cancellation_reason'),
            );
        } catch (InvalidArgumentException|InsufficientStockException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('inventory.stock_adjustments.show', $stockAdjustment)
            ->with('success', 'Stock adjustment cancelled successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'products' => Product::where('is_active', true)->orderBy('name')->get(['id', 'code', 'name', 'unit']),
            'defaultWarehouseId' => auth()->user()->default_warehouse_id,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function syncItems(StockAdjustment $adjustment, array $items): void
    {
        foreach ($items as $item) {
            $adjustment->items()->create([
                'product_id' => $item['product_id'],
                'direction' => $item['direction'],
                'quantity' => $item['quantity'],
                'notes' => $item['notes'] ?? null,
            ]);
        }
    }
}
