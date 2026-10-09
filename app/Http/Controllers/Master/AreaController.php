<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreAreaRequest;
use App\Http\Requests\Master\UpdateAreaRequest;
use App\Models\Area;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AreaController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('areas.view');

        $areas = Area::query()
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

        return view('master.areas.index', compact('areas'));
    }

    public function create(): View
    {
        $this->authorize('areas.create');

        return view('master.areas.create', ['area' => null]);
    }

    public function store(StoreAreaRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Area::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('master.areas.index')
            ->with('success', 'Area created successfully.');
    }

    public function edit(Area $area): View
    {
        $this->authorize('areas.edit');

        return view('master.areas.edit', compact('area'));
    }

    public function update(UpdateAreaRequest $request, Area $area): RedirectResponse
    {
        $validated = $request->validated();

        $area->update([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', false),
        ]);

        return redirect()
            ->route('master.areas.index')
            ->with('success', 'Area updated successfully.');
    }

    public function destroy(Area $area): RedirectResponse
    {
        $this->authorize('areas.delete');

        if ($area->customers()->exists() || $area->salesmen()->exists() || $area->sales()->exists()) {
            return back()->with('error', 'This area has related records and cannot be deleted.');
        }

        $area->delete();

        return redirect()
            ->route('master.areas.index')
            ->with('success', 'Area deleted successfully.');
    }
}
