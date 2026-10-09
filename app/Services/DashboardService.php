<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\ProductWarehouseStock;
use App\Models\Sale;

class DashboardService
{
    public function __construct(
        private CustomerLedgerService $ledgerService,
    ) {}

    /**
     * Total inventory value (quantity × purchase rate) across all warehouses.
     */
    public function getTotalStockValue(): string
    {
        $total = ProductWarehouseStock::query()
            ->join('products', 'products.id', '=', 'product_warehouse_stock.product_id')
            ->where('products.is_active', true)
            ->selectRaw('COALESCE(SUM(product_warehouse_stock.quantity * products.purchase_rate), 0) as total')
            ->value('total');

        return bcadd((string) $total, '0', 2);
    }

    /**
     * Sum of posted sales for today.
     */
    public function getTodaysSalesTotal(): string
    {
        $total = Sale::query()
            ->where('status', 'posted')
            ->whereDate('sale_date', today())
            ->sum('total_amount');

        return bcadd((string) $total, '0', 2);
    }

    /**
     * Count of product/warehouse rows at or below minimum stock level.
     */
    public function getLowStockCount(): int
    {
        return ProductWarehouseStock::query()
            ->whereHas('product', function ($query) {
                $query->where('is_active', true)
                    ->where('min_stock_level', '>', 0)
                    ->whereColumn('product_warehouse_stock.quantity', '<=', 'products.min_stock_level');
            })
            ->count();
    }

    /**
     * Sum of positive customer balances (amounts owed to us).
     */
    public function getOutstandingReceivables(): string
    {
        $total = '0';

        Customer::query()
            ->where('is_active', true)
            ->pluck('id')
            ->each(function (int $customerId) use (&$total) {
                $balance = $this->ledgerService->getBalance($customerId);

                if (bccomp($balance, '0', 2) > 0) {
                    $total = bcadd($total, $balance, 2);
                }
            });

        return $total;
    }

    /**
     * Recent posted sales for the dashboard activity feed.
     */
    public function getRecentSales(int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return Sale::query()
            ->with(['customer', 'warehouse'])
            ->where('status', 'posted')
            ->orderByDesc('sale_date')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /**
     * Top low-stock items for quick visibility on the dashboard.
     */
    public function getLowStockItems(int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return ProductWarehouseStock::query()
            ->with(['product', 'warehouse'])
            ->whereHas('product', function ($query) {
                $query->where('is_active', true)
                    ->where('min_stock_level', '>', 0)
                    ->whereColumn('product_warehouse_stock.quantity', '<=', 'products.min_stock_level');
            })
            ->orderBy('quantity')
            ->limit($limit)
            ->get();
    }
}
