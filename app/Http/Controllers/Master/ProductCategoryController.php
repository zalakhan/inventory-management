<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreProductCategoryRequest;
use App\Http\Requests\Master\UpdateProductCategoryRequest;
use App\Models\ProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('categories.view');

        $categories = ProductCategory::query()
            ->with('parent')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('master.categories.index', compact('categories'));
    }

    public function create(): View
    {
        $this->authorize('categories.create');

        return view('master.categories.create', [
            'category' => null,
            'parentCategories' => $this->parentOptions(),
        ]);
    }

    public function store(StoreProductCategoryRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        ProductCategory::create([
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('master.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(ProductCategory $product_category): View
    {
        $this->authorize('categories.edit');

        return view('master.categories.edit', [
            'category' => $product_category,
            'parentCategories' => $this->parentOptions($product_category),
        ]);
    }

    public function update(UpdateProductCategoryRequest $request, ProductCategory $product_category): RedirectResponse
    {
        $validated = $request->validated();

        if (! empty($validated['parent_id']) && $this->isDescendant($product_category, (int) $validated['parent_id'])) {
            return back()
                ->withInput()
                ->with('error', 'A category cannot be moved under one of its own subcategories.');
        }

        $product_category->update([
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', false),
        ]);

        return redirect()
            ->route('master.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(ProductCategory $product_category): RedirectResponse
    {
        $this->authorize('categories.delete');

        if ($product_category->products()->exists() || $product_category->children()->exists()) {
            return back()->with('error', 'This category has products or subcategories and cannot be deleted.');
        }

        $product_category->delete();

        return redirect()
            ->route('master.categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, ProductCategory>
     */
    private function parentOptions(?ProductCategory $exclude = null)
    {
        return ProductCategory::query()
            ->when($exclude, fn ($query) => $query->where('id', '!=', $exclude->id))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    private function isDescendant(ProductCategory $category, int $parentId): bool
    {
        $current = ProductCategory::find($parentId);

        while ($current) {
            if ($current->id === $category->id) {
                return true;
            }

            $current = $current->parent;
        }

        return false;
    }
}
