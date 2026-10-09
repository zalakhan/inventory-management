<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductWarehouseStock;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\CustomerLedgerService;
use App\Services\SalePostingService;
use App\Services\StockReceivingPostingService;
use App\Models\StockReceiving;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SaleTest extends TestCase
{
    use RefreshDatabase;

    protected function seedSettings(): void
    {
        Setting::updateOrCreate(['key' => 'sales.invoice_prefix'], ['group' => 'numbering', 'value' => 'INV-', 'type' => 'string']);
        Setting::updateOrCreate(['key' => 'sales.invoice_next'], ['group' => 'numbering', 'value' => '1', 'type' => 'integer']);
        Setting::updateOrCreate(['key' => 'payments.receipt_prefix'], ['group' => 'numbering', 'value' => 'RCP-', 'type' => 'string']);
        Setting::updateOrCreate(['key' => 'payments.receipt_next'], ['group' => 'numbering', 'value' => '1', 'type' => 'integer']);
        Setting::updateOrCreate(['key' => 'stock_receiving.receipt_prefix'], ['group' => 'numbering', 'value' => 'GRN-', 'type' => 'string']);
        Setting::updateOrCreate(['key' => 'stock_receiving.receipt_next'], ['group' => 'numbering', 'value' => '1', 'type' => 'integer']);
    }

    /**
     * @return array{user: User, warehouse: Warehouse, product: Product, customer: Customer}
     */
    protected function createSaleFixtures(int $stockQty = 100): array
    {
        $this->seedSettings();

        $user = User::factory()->create();

        $warehouse = Warehouse::create([
            'code' => 'WH-SALE',
            'name' => 'Sale Warehouse',
            'is_default' => true,
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'code' => 'CUS-001',
            'name' => 'Test Customer',
            'opening_balance' => 0,
            'is_active' => true,
        ]);

        $category = ProductCategory::create([
            'name' => 'Sale Category',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'code' => 'PRD-SALE',
            'name' => 'Sale Product',
            'unit' => 'pcs',
            'purchase_rate' => 5,
            'sale_rate' => 10,
            'is_active' => true,
        ]);

        $supplier = Supplier::create([
            'code' => 'SUP-SALE',
            'name' => 'Sale Supplier',
            'is_active' => true,
        ]);

        $receiving = StockReceiving::create([
            'receipt_number' => 'GRN-SALE-001',
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'receiving_date' => now(),
            'subtotal' => 500,
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        $receiving->items()->create([
            'product_id' => $product->id,
            'quantity' => $stockQty,
            'purchase_rate' => 5,
            'line_total' => bcmul((string) $stockQty, '5', 2),
        ]);

        app(StockReceivingPostingService::class)->post($receiving->fresh('items.product'), $user);

        return compact('user', 'warehouse', 'product', 'customer');
    }

    public function test_posting_sale_deducts_stock_and_updates_customer_ledger(): void
    {
        ['user' => $user, 'warehouse' => $warehouse, 'product' => $product, 'customer' => $customer] = $this->createSaleFixtures();

        $sale = Sale::create([
            'invoice_number' => 'INV-00001',
            'warehouse_id' => $warehouse->id,
            'customer_id' => $customer->id,
            'sale_date' => now(),
            'subtotal' => 100,
            'discount_amount' => 0,
            'total_amount' => 100,
            'amount_paid' => 40,
            'outstanding_amount' => 60,
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        $sale->items()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'rate' => 10,
            'discount_amount' => 0,
            'line_total' => 100,
        ]);

        $posted = app(SalePostingService::class)->post($sale->fresh('items.product'), $user);

        $this->assertSame('posted', $posted->status);

        $stock = ProductWarehouseStock::query()
            ->where('warehouse_id', $warehouse->id)
            ->where('product_id', $product->id)
            ->first();

        $this->assertSame('90.0000', (string) $stock->quantity);
        $this->assertSame(1, $posted->stockMovements()->where('status', 'posted')->count());

        $balance = app(CustomerLedgerService::class)->getBalance($customer->id);
        $this->assertSame('60.00', $balance);

        $this->assertSame(1, $posted->ledgerEntries()->where('status', 'posted')->count());
        $this->assertDatabaseHas('customer_payments', [
            'customer_id' => $customer->id,
            'amount' => 40,
            'status' => 'posted',
        ]);
    }

    public function test_cancelling_posted_sale_reverses_stock_and_ledger(): void
    {
        ['user' => $user, 'warehouse' => $warehouse, 'product' => $product, 'customer' => $customer] = $this->createSaleFixtures();

        $sale = Sale::create([
            'invoice_number' => 'INV-00002',
            'warehouse_id' => $warehouse->id,
            'customer_id' => $customer->id,
            'sale_date' => now(),
            'subtotal' => 50,
            'discount_amount' => 0,
            'total_amount' => 50,
            'amount_paid' => 0,
            'outstanding_amount' => 50,
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        $sale->items()->create([
            'product_id' => $product->id,
            'quantity' => 5,
            'rate' => 10,
            'discount_amount' => 0,
            'line_total' => 50,
        ]);

        $postingService = app(SalePostingService::class);
        $posted = $postingService->post($sale->fresh('items.product'), $user);
        $cancelled = $postingService->cancel($posted, $user, 'Wrong customer');

        $this->assertSame('cancelled', $cancelled->status);

        $stock = ProductWarehouseStock::query()
            ->where('warehouse_id', $warehouse->id)
            ->where('product_id', $product->id)
            ->first();

        $this->assertSame('100.0000', (string) $stock->quantity);

        $balance = app(CustomerLedgerService::class)->getBalance($customer->id);
        $this->assertSame('0.00', $balance);
    }

    public function test_sales_invoice_pages_require_permission(): void
    {
        $this->seedSettings();

        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('sales.invoices.index'))->assertForbidden();

        $role = Role::create(['name' => 'sales_viewer']);
        Permission::create(['name' => 'sales.view']);
        $user->assignRole($role);
        $role->givePermissionTo('sales.view');

        $this->get(route('sales.invoices.index'))->assertOk();
    }
}
