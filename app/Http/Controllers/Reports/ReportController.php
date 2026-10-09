<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Warehouse;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $reportService,
    ) {}

    /**
     * Reports hub — links to all available reports.
     */
    public function index(): View
    {
        $this->authorize('reports.view');

        return view('reports.index');
    }

    /**
     * Stock summary by product and warehouse.
     */
    public function stockSummary(Request $request): View|StreamedResponse
    {
        $this->authorize('reports.view');

        $query = $this->reportService->stockSummaryQuery($request);

        if ($request->input('export') === 'csv') {
            $this->authorize('reports.export');

            return $this->exportStockSummary($query->get());
        }

        $rows = $query->paginate(25)->withQueryString();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $categories = ProductCategory::where('is_active', true)->orderBy('name')->get();

        return view('reports.stock-summary', compact('rows', 'warehouses', 'categories'));
    }

    /**
     * Posted sales register.
     */
    public function salesRegister(Request $request): View|StreamedResponse
    {
        $this->authorize('reports.view');

        $query = $this->reportService->salesRegisterQuery($request);

        if ($request->input('export') === 'csv') {
            $this->authorize('reports.export');

            return $this->exportSalesRegister($query->get());
        }

        $sales = $query->paginate(25)->withQueryString();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $customers = Customer::where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']);

        return view('reports.sales-register', compact('sales', 'warehouses', 'customers'));
    }

    /**
     * Customers with outstanding balances.
     */
    public function customerOutstanding(Request $request): View|StreamedResponse
    {
        $this->authorize('reports.view');

        $rows = $this->reportService->customerOutstandingRows($request);

        if ($request->input('export') === 'csv') {
            $this->authorize('reports.export');

            return $this->exportCustomerOutstanding($rows);
        }

        $areas = Area::where('is_active', true)->orderBy('name')->get();

        return view('reports.customer-outstanding', compact('rows', 'areas'));
    }

    /**
     * Stock movement report.
     */
    public function stockMovements(Request $request): View|StreamedResponse
    {
        $this->authorize('reports.view');

        $query = $this->reportService->stockMovementsQuery($request);

        if ($request->input('export') === 'csv') {
            $this->authorize('reports.export');

            return $this->exportStockMovements($query->get());
        }

        $movements = $query->paginate(25)->withQueryString();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']);

        return view('reports.stock-movements', compact('movements', 'warehouses', 'products'));
    }

    /**
     * Posted payments register.
     */
    public function paymentRegister(Request $request): View|StreamedResponse
    {
        $this->authorize('reports.view');

        $query = $this->reportService->paymentRegisterQuery($request);

        if ($request->input('export') === 'csv') {
            $this->authorize('reports.export');

            return $this->exportPaymentRegister($query->get());
        }

        $payments = $query->paginate(25)->withQueryString();
        $customers = Customer::where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']);

        return view('reports.payment-register', compact('payments', 'customers'));
    }

    protected function exportStockSummary($rows): StreamedResponse
    {
        $csvRows = $rows->map(function ($row) {
            $value = bcmul((string) $row->quantity, (string) ($row->product?->purchase_rate ?? 0), 2);

            return [
                $row->warehouse?->name,
                $row->product?->code,
                $row->product?->name,
                $row->product?->category?->name,
                number_format($row->quantity, 4, '.', ''),
                number_format($row->product?->purchase_rate ?? 0, 4, '.', ''),
                $value,
            ];
        });

        return $this->reportService->csvResponse(
            ['Warehouse', 'Product Code', 'Product Name', 'Category', 'Quantity', 'Purchase Rate', 'Stock Value'],
            $csvRows,
            'stock-summary-'.now()->format('Y-m-d').'.csv'
        );
    }

    protected function exportSalesRegister($sales): StreamedResponse
    {
        $csvRows = $sales->map(fn ($sale) => [
            $sale->invoice_number,
            $sale->sale_date?->format('Y-m-d'),
            $sale->customer?->name,
            $sale->warehouse?->name,
            $sale->salesman?->name,
            number_format($sale->subtotal, 2, '.', ''),
            number_format($sale->discount_amount, 2, '.', ''),
            number_format($sale->total_amount, 2, '.', ''),
            number_format($sale->amount_paid, 2, '.', ''),
            number_format($sale->outstanding_amount, 2, '.', ''),
        ]);

        return $this->reportService->csvResponse(
            ['Invoice', 'Date', 'Customer', 'Warehouse', 'Salesman', 'Subtotal', 'Discount', 'Total', 'Paid', 'Outstanding'],
            $csvRows,
            'sales-register-'.now()->format('Y-m-d').'.csv'
        );
    }

    protected function exportCustomerOutstanding(array $rows): StreamedResponse
    {
        $csvRows = collect($rows)->map(fn ($row) => [
            $row['customer']->code,
            $row['customer']->name,
            $row['customer']->area?->name,
            $row['customer']->phone,
            number_format((float) $row['balance'], 2, '.', ''),
        ]);

        return $this->reportService->csvResponse(
            ['Customer Code', 'Customer Name', 'Area', 'Phone', 'Outstanding'],
            $csvRows,
            'customer-outstanding-'.now()->format('Y-m-d').'.csv'
        );
    }

    protected function exportStockMovements($movements): StreamedResponse
    {
        $csvRows = $movements->map(fn ($m) => [
            $m->movement_date?->format('Y-m-d'),
            $m->warehouse?->name,
            $m->product?->code,
            $m->product?->name,
            strtoupper($m->direction),
            number_format($m->quantity, 4, '.', ''),
            number_format($m->balance_after, 4, '.', ''),
            class_basename($m->source_type ?? ''),
            $m->notes,
        ]);

        return $this->reportService->csvResponse(
            ['Date', 'Warehouse', 'Product Code', 'Product Name', 'Direction', 'Quantity', 'Balance After', 'Source', 'Notes'],
            $csvRows,
            'stock-movements-'.now()->format('Y-m-d').'.csv'
        );
    }

    protected function exportPaymentRegister($payments): StreamedResponse
    {
        $csvRows = $payments->map(fn ($p) => [
            $p->receipt_number,
            $p->payment_date?->format('Y-m-d'),
            $p->customer?->name,
            str_replace('_', ' ', ucfirst($p->payment_method)),
            number_format($p->amount, 2, '.', ''),
            $p->reference_no,
        ]);

        return $this->reportService->csvResponse(
            ['Receipt', 'Date', 'Customer', 'Method', 'Amount', 'Reference'],
            $csvRows,
            'payment-register-'.now()->format('Y-m-d').'.csv'
        );
    }
}
