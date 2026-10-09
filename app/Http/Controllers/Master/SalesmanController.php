<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreSalesmanRequest;
use App\Http\Requests\Master\UpdateSalesmanRequest;
use App\Models\Area;
use App\Models\Salesman;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesmanController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('salesmen.view');

        $salesmen = Salesman::query()
            ->with(['area', 'user'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('master.salesmen.index', compact('salesmen'));
    }

    public function create(): View
    {
        $this->authorize('salesmen.create');

        return view('master.salesmen.create', [
            'salesman' => null,
            'areas' => Area::where('is_active', true)->orderBy('name')->get(),
            'users' => User::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreSalesmanRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Salesman::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'area_id' => $validated['area_id'] ?? null,
            'user_id' => $validated['user_id'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('master.salesmen.index')
            ->with('success', 'Salesman created successfully.');
    }

    public function edit(Salesman $salesman): View
    {
        $this->authorize('salesmen.edit');

        return view('master.salesmen.edit', [
            'salesman' => $salesman,
            'areas' => Area::where('is_active', true)->orderBy('name')->get(),
            'users' => User::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateSalesmanRequest $request, Salesman $salesman): RedirectResponse
    {
        $validated = $request->validated();

        $salesman->update([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'area_id' => $validated['area_id'] ?? null,
            'user_id' => $validated['user_id'] ?? null,
            'is_active' => $request->boolean('is_active', false),
        ]);

        return redirect()
            ->route('master.salesmen.index')
            ->with('success', 'Salesman updated successfully.');
    }

    public function destroy(Salesman $salesman): RedirectResponse
    {
        $this->authorize('salesmen.delete');

        if ($salesman->sales()->exists()) {
            return back()->with('error', 'This salesman has sales records and cannot be deleted.');
        }

        $salesman->delete();

        return redirect()
            ->route('master.salesmen.index')
            ->with('success', 'Salesman deleted successfully.');
    }
}
