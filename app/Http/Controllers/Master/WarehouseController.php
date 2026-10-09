<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreWarehouseRequest;
use App\Http\Requests\Master\UpdateWarehouseRequest;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('warehouses.view');

        $warehouses = Warehouse::query()
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

        return view('master.warehouses.index', compact('warehouses'));
    }

    public function create(): View
    {
        $this->authorize('warehouses.create');

        return view('master.warehouses.create', ['warehouse' => null]);
    }

    public function store(StoreWarehouseRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($request, $validated) {
            $warehouse = Warehouse::create([
                'code' => $validated['code'],
                'name' => $validated['name'],
                'address' => $validated['address'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'is_default' => $request->boolean('is_default'),
                'is_active' => $request->boolean('is_active', true),
            ]);

            if ($warehouse->is_default) {
                $this->clearOtherDefaults($warehouse);
            }
        });

        return redirect()
            ->route('master.warehouses.index')
            ->with('success', 'Warehouse created successfully.');
    }

    public function edit(Warehouse $warehouse): View
    {
        $this->authorize('warehouses.edit');

        return view('master.warehouses.edit', compact('warehouse'));
    }

    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($request, $validated, $warehouse) {
            $warehouse->update([
                'code' => $validated['code'],
                'name' => $validated['name'],
                'address' => $validated['address'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'is_default' => $request->boolean('is_default'),
                'is_active' => $request->boolean('is_active', false),
            ]);

            if ($warehouse->is_default) {
                $this->clearOtherDefaults($warehouse);
            }
        });

        return redirect()
            ->route('master.warehouses.index')
            ->with('success', 'Warehouse updated successfully.');
    }

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        $this->authorize('warehouses.delete');

        if ($warehouse->is_default) {
            return back()->with('error', 'Cannot delete the default warehouse. Set another warehouse as default first.');
        }

        if ($warehouse->stockMovements()->exists()
            || $warehouse->stockReceivings()->exists()
            || $warehouse->sales()->exists()
            || $warehouse->users()->exists()) {
            return back()->with('error', 'This warehouse has related records and cannot be deleted.');
        }

        $warehouse->delete();

        return redirect()
            ->route('master.warehouses.index')
            ->with('success', 'Warehouse deleted successfully.');
    }

    private function clearOtherDefaults(Warehouse $warehouse): void
    {
        Warehouse::where('id', '!=', $warehouse->id)
            ->where('is_default', true)
            ->update(['is_default' => false]);
    }
}
