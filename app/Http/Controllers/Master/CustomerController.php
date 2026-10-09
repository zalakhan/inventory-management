<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreCustomerRequest;
use App\Http\Requests\Master\UpdateCustomerRequest;
use App\Models\Area;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
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
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('master.customers.index', compact('customers'));
    }

    public function create(): View
    {
        $this->authorize('customers.create');

        return view('master.customers.create', [
            'customer' => null,
            'areas' => Area::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Customer::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'area_id' => $validated['area_id'] ?? null,
            'opening_balance' => $validated['opening_balance'] ?? 0,
            'credit_limit' => $validated['credit_limit'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('master.customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function edit(Customer $customer): View
    {
        $this->authorize('customers.edit');

        return view('master.customers.edit', [
            'customer' => $customer,
            'areas' => Area::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validated();

        $customer->update([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'area_id' => $validated['area_id'] ?? null,
            'opening_balance' => $validated['opening_balance'] ?? 0,
            'credit_limit' => $validated['credit_limit'] ?? null,
            'is_active' => $request->boolean('is_active', false),
        ]);

        return redirect()
            ->route('master.customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $this->authorize('customers.delete');

        if ($customer->sales()->exists()
            || $customer->payments()->exists()
            || $customer->ledgerEntries()->exists()) {
            return back()->with('error', 'This customer has transaction history and cannot be deleted.');
        }

        $customer->delete();

        return redirect()
            ->route('master.customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}
