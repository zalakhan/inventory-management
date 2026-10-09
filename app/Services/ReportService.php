<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\ProductWarehouseStock;
use App\Models\Sale;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportService
{
    public function __construct(
        private CustomerLedgerService $ledgerService,
    ) {}

    /**
     * Stock summary rows grouped by product and warehouse.
     */
    public function stockSummaryQuery(Request $request): Builder
    {
        return ProductWarehouseStock::query()
            ->with(['product.category', 'warehouse'])
            ->when($request->filled('warehouse_id'), fn ($q) => $q->where('warehouse_id', $request->integer('warehouse_id')))
            ->when($request->filled('category_id'), function ($q) use ($request) {
                $q->whereHas('product', fn ($pq) => $pq->where('category_id', $request->integer('category_id')));
            })
            ->when($request->boolean('low_stock'), function ($q) {
                $q->whereHas('product', function ($pq) {
                    $pq->whereColumn('product_warehouse_stock.quantity', '<=', 'products.min_stock_level')
                        ->where('products.min_stock_level', '>', 0);
                });
            })
            ->whereHas('product', fn ($q) => $q->where('is_active', true))
            ->orderBy('warehouse_id')
            ->orderBy('product_id');
    }

    /**
     * Posted sales register with optional filters.
     */
    public function salesRegisterQuery(Request $request): Builder
    {
        return Sale::query()
            ->with(['customer', 'warehouse', 'salesman'])
            ->where('status', 'posted')
            ->when($request->filled('warehouse_id'), fn ($q) => $q->where('warehouse_id', $request->integer('warehouse_id')))
            ->when($request->filled('customer_id'), fn ($q) => $q->where('customer_id', $request->integer('customer_id')))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('sale_date', '>=', $request->string('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('sale_date', '<=', $request->string('date_to')))
            ->orderByDesc('sale_date')
            ->orderByDesc('id');
    }

    /**
     * Active customers with positive outstanding balances.
     */
    public function customerOutstandingRows(Request $request): array
    {
        $customers = Customer::query()
            ->with('area')
            ->where('is_active', true)
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(function ($inner) use ($search) {
                    $inner->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('area_id'), fn ($q) => $q->where('area_id', $request->integer('area_id')))
            ->orderBy('name')
            ->get();

        $rows = [];

        foreach ($customers as $customer) {
            $balance = $this->ledgerService->getBalance($customer->id);

            if (bccomp($balance, '0', 2) <= 0) {
                continue;
            }

            $rows[] = [
                'customer' => $customer,
                'balance' => $balance,
            ];
        }

        if ($request->boolean('sort_desc')) {
            usort($rows, fn ($a, $b) => bccomp($b['balance'], $a['balance'], 2));
        }

        return $rows;
    }

    /**
     * Stock movement ledger for reporting.
     */
    public function stockMovementsQuery(Request $request): Builder
    {
        return StockMovement::query()
            ->with(['product', 'warehouse', 'creator'])
            ->where('status', 'posted')
            ->when($request->filled('warehouse_id'), fn ($q) => $q->where('warehouse_id', $request->integer('warehouse_id')))
            ->when($request->filled('product_id'), fn ($q) => $q->where('product_id', $request->integer('product_id')))
            ->when($request->filled('direction'), fn ($q) => $q->where('direction', $request->string('direction')))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('movement_date', '>=', $request->string('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('movement_date', '<=', $request->string('date_to')))
            ->orderByDesc('movement_date')
            ->orderByDesc('id');
    }

    /**
     * Posted customer payments register.
     */
    public function paymentRegisterQuery(Request $request): Builder
    {
        return CustomerPayment::query()
            ->with(['customer'])
            ->where('status', 'posted')
            ->when($request->filled('customer_id'), fn ($q) => $q->where('customer_id', $request->integer('customer_id')))
            ->when($request->filled('payment_method'), fn ($q) => $q->where('payment_method', $request->string('payment_method')))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('payment_date', '>=', $request->string('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('payment_date', '<=', $request->string('date_to')))
            ->orderByDesc('payment_date')
            ->orderByDesc('id');
    }

    /**
     * Stream a CSV download response.
     */
    public function csvResponse(array $headers, iterable $rows, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
