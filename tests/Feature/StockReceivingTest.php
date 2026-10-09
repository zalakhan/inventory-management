<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductWarehouseStock;
use App\Models\StockReceiving;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\DocumentNumberService;
use App\Services\StockReceivingPostingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StockReceivingTest extends TestCase
{
    use RefreshDatabase;

    public function test_posting_stock_receiving_updates_stock_and_creates_movements(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'test_admin']);
        Permission::create(['name' => 'stock_receiving.post']);
        $user->assignRole($role);
        $role->givePermissionTo('stock_receiving.post');

        $warehouse = Warehouse::create([
            'code' => 'WH-TEST',
            'name' => 'Test Warehouse',
            'is_default' => true,
            'is_active' => true,
        ]);

        $supplier = Supplier::create([
            'code' => 'SUP-TEST',
            'name' => 'Test Supplier',
            'is_active' => true,
        ]);

        $category = \App\Models\ProductCategory::create([
            'name' => 'Test Category',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'code' => 'PRD-TEST',
            'name' => 'Test Product',
            'unit' => 'pcs',
            'is_active' => true,
        ]);

        $receiptNumber = app(DocumentNumberService::class)->next(
            'stock_receiving.receipt_prefix',
            'stock_receiving.receipt_next',
        );

        $receiving = StockReceiving::create([
            'receipt_number' => $receiptNumber,
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'receiving_date' => now(),
            'subtotal' => 50,
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        $receiving->items()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'purchase_rate' => 5,
            'line_total' => 50,
        ]);

        $posted = app(StockReceivingPostingService::class)->post($receiving->fresh('items.product'), $user);

        $this->assertSame('posted', $posted->status);

        $stock = ProductWarehouseStock::query()
            ->where('warehouse_id', $warehouse->id)
            ->where('product_id', $product->id)
            ->first();

        $this->assertNotNull($stock);
        $this->assertSame('10.0000', (string) $stock->quantity);
        $this->assertSame(1, $posted->stockMovements()->where('status', 'posted')->count());
    }

    public function test_cancelling_posted_receiving_reverses_stock(): void
    {
        $user = User::factory()->create();

        $warehouse = Warehouse::create([
            'code' => 'WH-TEST2',
            'name' => 'Test Warehouse 2',
            'is_default' => false,
            'is_active' => true,
        ]);

        $supplier = Supplier::create([
            'code' => 'SUP-TEST2',
            'name' => 'Test Supplier 2',
            'is_active' => true,
        ]);

        $category = \App\Models\ProductCategory::create([
            'name' => 'Test Category 2',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'code' => 'PRD-TEST2',
            'name' => 'Test Product 2',
            'unit' => 'pcs',
            'is_active' => true,
        ]);

        $receiving = StockReceiving::create([
            'receipt_number' => 'GRN-TEST-001',
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'receiving_date' => now(),
            'subtotal' => 50,
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        $receiving->items()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'purchase_rate' => 5,
            'line_total' => 50,
        ]);

        $postingService = app(StockReceivingPostingService::class);
        $posted = $postingService->post($receiving->fresh('items.product'), $user);
        $cancelled = $postingService->cancel($posted, $user, 'Wrong supplier invoice');

        $this->assertSame('cancelled', $cancelled->status);

        $stock = ProductWarehouseStock::query()
            ->where('warehouse_id', $warehouse->id)
            ->where('product_id', $product->id)
            ->first();

        $this->assertSame('0.0000', (string) $stock->quantity);
        $this->assertSame(2, $posted->stockMovements()->count());
    }
}
