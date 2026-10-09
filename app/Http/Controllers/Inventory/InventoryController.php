<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductWarehouseStock;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    /**
     * Current stock levels per product and warehouse.
     */
    public function index(Request $request): View
    {
        $this->authorize('inventory.view');

        $stock = ProductWarehouseStock::query()
            ->with(['product.category', 'warehouse'])
            ->when($request->filled('warehouse_id'), fn ($query) => $query->where('warehouse_id', $request->integer('warehouse_id')))
            ->when($request->filled('product_id'), fn ($query) => $query->where('product_id', $request->integer('product_id')))
            ->when($request->boolean('low_stock'), function ($query) {
                $query->whereHas('product', function ($productQuery) {
                    $productQuery->whereColumn('product_warehouse_stock.quantity', '<=', 'products.min_stock_level')
                        ->where('products.min_stock_level', '>', 0);
                });
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->whereHas('product', function ($productQuery) use ($search) {
                    $productQuery->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->orderBy(
                Warehouse::select('name')
                    ->whereColumn('warehouses.id', 'product_warehouse_stock.warehouse_id')
                    ->limit(1)
            )
            ->orderBy(
                Product::select('name')
                    ->whereColumn('products.id', 'product_warehouse_stock.product_id')
                    ->limit(1)
            )
            ->paginate(20)
            ->withQueryString();

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']);

        return view('inventory.stock.index', compact('stock', 'warehouses', 'products'));
    }

    /**
     * Read-only stock movement ledger with filters.
     */
    public function movements(Request $request): View
    {
        $this->authorize('inventory.view');

        $movements = StockMovement::query()
            ->with(['product', 'warehouse', 'creator', 'source'])
            ->when($request->filled('warehouse_id'), fn ($query) => $query->where('warehouse_id', $request->integer('warehouse_id')))
            ->when($request->filled('product_id'), fn ($query) => $query->where('product_id', $request->integer('product_id')))
            ->when($request->filled('direction'), fn ($query) => $query->where('direction', $request->string('direction')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('movement_date', '>=', $request->string('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('movement_date', '<=', $request->string('date_to')))
            ->orderByDesc('movement_date')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']);

        return view('inventory.movements.index', compact('movements', 'warehouses', 'products'));
    }
}
