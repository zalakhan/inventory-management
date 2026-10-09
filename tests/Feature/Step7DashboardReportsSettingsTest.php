<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductWarehouseStock;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\StockReceiving;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\DashboardService;
use App\Services\PaymentPostingService;
use App\Services\SalePostingService;
use App\Services\StockReceivingPostingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class Step7DashboardReportsSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function seedSettings(): void
    {
        $settings = [
            ['group' => 'general', 'key' => 'company_name', 'value' => 'Prime Distributions', 'type' => 'string'],
            ['group' => 'general', 'key' => 'company_phone', 'value' => '0300-0000000', 'type' => 'string'],
            ['group' => 'general', 'key' => 'company_address', 'value' => 'Test Address', 'type' => 'string'],
            ['group' => 'numbering', 'key' => 'sales.invoice_prefix', 'value' => 'INV-', 'type' => 'string'],
            ['group' => 'numbering', 'key' => 'sales.invoice_next', 'value' => '1', 'type' => 'integer'],
            ['group' => 'numbering', 'key' => 'stock_receiving.receipt_prefix', 'value' => 'GRN-', 'type' => 'string'],
            ['group' => 'numbering', 'key' => 'stock_receiving.receipt_next', 'value' => '1', 'type' => 'integer'],
            ['group' => 'numbering', 'key' => 'payments.receipt_prefix', 'value' => 'RCP-', 'type' => 'string'],
            ['group' => 'numbering', 'key' => 'payments.receipt_next', 'value' => '1', 'type' => 'integer'],
            ['group' => 'numbering', 'key' => 'sale_returns.return_prefix', 'value' => 'SRN-', 'type' => 'string'],
            ['group' => 'numbering', 'key' => 'sale_returns.return_next', 'value' => '1', 'type' => 'integer'],
            ['group' => 'numbering', 'key' => 'stock_adjustments.adjustment_prefix', 'value' => 'ADJ-', 'type' => 'string'],
            ['group' => 'numbering', 'key' => 'stock_adjustments.adjustment_next', 'value' => '1', 'type' => 'integer'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }

    protected function userWithPermissions(array $permissions): User
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'step7_tester_'.uniqid()]);
        $user->assignRole($role);

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
            $role->givePermissionTo($permission);
        }

        return $user;
    }

    /**
     * @return array{user: User, warehouse: Warehouse, product: Product, customer: Customer}
     */
    protected function createFixtures(): array
    {
        $this->seedSettings();

        $user = User::factory()->create();

        $warehouse = Warehouse::create([
            'code' => 'WH-STEP7',
            'name' => 'Step 7 Warehouse',
            'is_default' => true,
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'code' => 'CUS-STEP7',
            'name' => 'Step 7 Customer',
            'opening_balance' => 0,
            'is_active' => true,
        ]);

        $category = ProductCategory::create([
            'name' => 'Step 7 Category',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'code' => 'PRD-STEP7',
            'name' => 'Step 7 Product',
            'unit' => 'pcs',
            'purchase_rate' => 5,
            'sale_rate' => 10,
            'min_stock_level' => 20,
            'is_active' => true,
        ]);

        $supplier = Supplier::create([
            'code' => 'SUP-STEP7',
            'name' => 'Step 7 Supplier',
            'is_active' => true,
        ]);

        $receiving = StockReceiving::create([
            'receipt_number' => 'GRN-STEP7-001',
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'receiving_date' => now(),
            'subtotal' => 500,
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        $receiving->items()->create([
            'product_id' => $product->id,
            'quantity' => 100,
            'purchase_rate' => 5,
            'line_total' => 500,
        ]);

        app(StockReceivingPostingService::class)->post($receiving->fresh('items.product'), $user);

        return compact('user', 'warehouse', 'product', 'customer');
    }

    public function test_dashboard_shows_live_kpis(): void
    {
        ['user' => $user, 'warehouse' => $warehouse, 'product' => $product, 'customer' => $customer] = $this->createFixtures();

        $sale = Sale::create([
            'invoice_number' => 'INV-STEP7-001',
            'warehouse_id' => $warehouse->id,
            'customer_id' => $customer->id,
            'sale_date' => today(),
            'subtotal' => 100,
            'discount_amount' => 0,
            'total_amount' => 100,
            'amount_paid' => 0,
            'outstanding_amount' => 100,
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

        app(SalePostingService::class)->post($sale->fresh('items.product'), $user);

        ProductWarehouseStock::query()
            ->where('warehouse_id', $warehouse->id)
            ->where('product_id', $product->id)
            ->update(['quantity' => 15]);

        $dashboard = app(DashboardService::class);

        $this->assertSame('75.00', $dashboard->getTotalStockValue());
        $this->assertSame('100.00', $dashboard->getTodaysSalesTotal());
        $this->assertSame(1, $dashboard->getLowStockCount());
        $this->assertSame('100.00', $dashboard->getOutstandingReceivables());

        $viewer = $this->userWithPermissions(['dashboard.view']);
        $this->actingAs($viewer)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('100.00')
            ->assertSee('Low Stock Items');
    }

    public function test_reports_pages_require_permission(): void
    {
        $this->seedSettings();
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('reports.index'))->assertForbidden();

        $viewer = $this->userWithPermissions(['reports.view']);
        $this->actingAs($viewer);

        $this->get(route('reports.index'))->assertOk();
        $this->get(route('reports.stock_summary'))->assertOk();
        $this->get(route('reports.sales_register'))->assertOk();
        $this->get(route('reports.customer_outstanding'))->assertOk();
        $this->get(route('reports.stock_movements'))->assertOk();
        $this->get(route('reports.payment_register'))->assertOk();
    }

    public function test_report_csv_export_requires_export_permission(): void
    {
        $this->createFixtures();

        $viewer = $this->userWithPermissions(['reports.view']);
        $this->actingAs($viewer)
            ->get(route('reports.stock_summary', ['export' => 'csv']))
            ->assertForbidden();

        $exporter = $this->userWithPermissions(['reports.view', 'reports.export']);
        $response = $this->actingAs($exporter)
            ->get(route('reports.stock_summary', ['export' => 'csv']));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));
    }

    public function test_settings_can_be_updated_by_authorized_user(): void
    {
        $this->seedSettings();

        $manager = $this->userWithPermissions(['settings.manage']);
        $this->actingAs($manager)
            ->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('Prime Distributions');

        $this->put(route('admin.settings.update'), [
            'settings' => [
                'company_name' => 'Updated Company',
                'company_phone' => '0300-1111111',
                'company_address' => 'New Address',
                'sales' => ['invoice_prefix' => 'INV-', 'invoice_next' => '10'],
                'stock_receiving' => ['receipt_prefix' => 'GRN-', 'receipt_next' => '5'],
                'payments' => ['receipt_prefix' => 'RCP-', 'receipt_next' => '3'],
                'sale_returns' => ['return_prefix' => 'SRN-', 'return_next' => '2'],
                'stock_adjustments' => ['adjustment_prefix' => 'ADJ-', 'adjustment_next' => '4'],
            ],
        ])
            ->assertRedirect(route('admin.settings.edit'))
            ->assertSessionHas('success');

        $this->assertSame('Updated Company', Setting::getValue('company_name'));
        $this->assertSame(10, Setting::getValue('sales.invoice_next'));
    }

    public function test_settings_page_is_forbidden_without_permission(): void
    {
        $this->seedSettings();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.settings.edit'))
            ->assertForbidden();
    }

    public function test_sales_register_shows_posted_sale(): void
    {
        ['user' => $user, 'warehouse' => $warehouse, 'product' => $product, 'customer' => $customer] = $this->createFixtures();

        $sale = Sale::create([
            'invoice_number' => 'INV-REG-001',
            'warehouse_id' => $warehouse->id,
            'customer_id' => $customer->id,
            'sale_date' => today(),
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

        app(SalePostingService::class)->post($sale->fresh('items.product'), $user);

        $viewer = $this->userWithPermissions(['reports.view']);
        $this->actingAs($viewer)
            ->get(route('reports.sales_register'))
            ->assertOk()
            ->assertSee('INV-REG-001');
    }

    public function test_payment_register_shows_posted_payment(): void
    {
        ['user' => $user, 'warehouse' => $warehouse, 'product' => $product, 'customer' => $customer] = $this->createFixtures();

        $sale = Sale::create([
            'invoice_number' => 'INV-PAY-001',
            'warehouse_id' => $warehouse->id,
            'customer_id' => $customer->id,
            'sale_date' => today(),
            'subtotal' => 100,
            'discount_amount' => 0,
            'total_amount' => 100,
            'amount_paid' => 0,
            'outstanding_amount' => 100,
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

        app(SalePostingService::class)->post($sale->fresh('items.product'), $user);

        $payment = CustomerPayment::create([
            'receipt_number' => 'RCP-REG-001',
            'customer_id' => $customer->id,
            'payment_date' => today(),
            'amount' => 40,
            'payment_method' => 'cash',
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        app(PaymentPostingService::class)->post($payment, $user);

        $viewer = $this->userWithPermissions(['reports.view']);
        $this->actingAs($viewer)
            ->get(route('reports.payment_register'))
            ->assertOk()
            ->assertSee('RCP-REG-001');
    }
}
