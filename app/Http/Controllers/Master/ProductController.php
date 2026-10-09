<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreProductRequest;
use App\Http\Requests\Master\UpdateProductRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('products.view');

        $products = Product::query()
            ->with('category')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $categories = ProductCategory::where('is_active', true)->orderBy('name')->get();

        return view('master.products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        $this->authorize('products.create');

        return view('master.products.create', [
            'product' => null,
            'categories' => ProductCategory::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Product::create([
            'category_id' => $validated['category_id'],
            'code' => $validated['code'],
            'name' => $validated['name'],
            'unit' => $validated['unit'],
            'purchase_rate' => $validated['purchase_rate'] ?? 0,
            'sale_rate' => $validated['sale_rate'] ?? 0,
            'min_stock_level' => $validated['min_stock_level'] ?? 0,
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('master.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product): View
    {
        $this->authorize('products.edit');

        return view('master.products.edit', [
            'product' => $product,
            'categories' => ProductCategory::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $validated = $request->validated();

        $product->update([
            'category_id' => $validated['category_id'],
            'code' => $validated['code'],
            'name' => $validated['name'],
            'unit' => $validated['unit'],
            'purchase_rate' => $validated['purchase_rate'] ?? 0,
            'sale_rate' => $validated['sale_rate'] ?? 0,
            'min_stock_level' => $validated['min_stock_level'] ?? 0,
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', false),
        ]);

        return redirect()
            ->route('master.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('products.delete');

        if ($product->stockMovements()->exists()
            || $product->stockReceivingItems()->exists()
            || $product->saleItems()->exists()
            || $product->warehouseStock()->where('quantity', '>', 0)->exists()) {
            return back()->with('error', 'This product has stock or transaction history and cannot be deleted.');
        }

        $product->delete();

        return redirect()
            ->route('master.products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
