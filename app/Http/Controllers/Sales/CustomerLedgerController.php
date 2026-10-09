<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use App\Services\CustomerLedgerService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerLedgerController extends Controller
{
    public function __construct(
        private CustomerLedgerService $ledgerService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('customers.view');

        $customers = Customer::query()
            ->with('area')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $balances = [];
        foreach ($customers as $customer) {
            $balances[$customer->id] = $this->ledgerService->getBalance($customer->id);
        }

        return view('sales.customer_ledger.index', compact('customers', 'balances'));
    }

    public function show(Request $request, Customer $customer): View
    {
        $this->authorize('customers.view');

        $entries = CustomerLedgerEntry::query()
            ->where('customer_id', $customer->id)
            ->when($request->filled('from_date'), fn ($query) => $query->whereDate('entry_date', '>=', $request->date('from_date')))
            ->when($request->filled('to_date'), fn ($query) => $query->whereDate('entry_date', '<=', $request->date('to_date')))
            ->when($request->filled('entry_type'), fn ($query) => $query->where('entry_type', $request->string('entry_type')))
            ->with(['creator', 'reference'])
            ->orderBy('entry_date')
            ->orderBy('id')
            ->paginate(25)
            ->withQueryString();

        $currentBalance = $this->ledgerService->getBalance($customer->id);

        return view('sales.customer_ledger.show', compact('customer', 'entries', 'currentBalance'));
    }
}
