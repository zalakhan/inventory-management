<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductWarehouseStock;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Setting;
use App\Models\StockAdjustment;
use App\Models\StockReceiving;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\CustomerLedgerService;
use App\Services\PaymentPostingService;
use App\Services\SalePostingService;
use App\Services\SaleReturnPostingService;
use App\Services\StockAdjustmentPostingService;
use App\Services\StockReceivingPostingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Step6PaymentsReturnsAdjustmentsTest extends TestCase
{
    use RefreshDatabase;

    protected function seedSettings(): void
    {
        $settings = [
            ['key' => 'payments.receipt_prefix', 'value' => 'RCP-'],
            ['key' => 'payments.receipt_next', 'value' => '1', 'type' => 'integer'],
            ['key' => 'sale_returns.return_prefix', 'value' => 'SRN-'],
            ['key' => 'sale_returns.return_next', 'value' => '1', 'type' => 'integer'],
            ['key' => 'stock_adjustments.adjustment_prefix', 'value' => 'ADJ-'],
            ['key' => 'stock_adjustments.adjustment_next', 'value' => '1', 'type' => 'integer'],
            ['key' => 'stock_receiving.receipt_prefix', 'value' => 'GRN-'],
            ['key' => 'stock_receiving.receipt_next', 'value' => '1', 'type' => 'integer'],
            ['key' => 'sales.invoice_prefix', 'value' => 'INV-'],
            ['key' => 'sales.invoice_next', 'value' => '1', 'type' => 'integer'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                ['group' => 'numbering', 'value' => $setting['value'], 'type' => $setting['type'] ?? 'string'],
            );
        }
    }

    /**
     * @return array{user: User, warehouse: Warehouse, product: Product, customer: Customer}
     */
    protected function createFixtures(int $stockQty = 100): array
    {
        $this->seedSettings();

        $user = User::factory()->create();

        $warehouse = Warehouse::create([
            'code' => 'WH-STEP6',
            'name' => 'Step 6 Warehouse',
            'is_default' => true,
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'code' => 'CUS-STEP6',
            'name' => 'Step 6 Customer',
            'opening_balance' => 0,
            'is_active' => true,
        ]);

        $category = ProductCategory::create([
            'name' => 'Step 6 Category',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'code' => 'PRD-STEP6',
            'name' => 'Step 6 Product',
            'unit' => 'pcs',
            'purchase_rate' => 5,
            'sale_rate' => 10,
            'is_active' => true,
        ]);

        $supplier = Supplier::create([
            'code' => 'SUP-STEP6',
            'name' => 'Step 6 Supplier',
            'is_active' => true,
        ]);

        $receiving = StockReceiving::create([
            'receipt_number' => 'GRN-STEP6-001',
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'receiving_date' => now(),
            'subtotal' => bcmul((string) $stockQty, '5', 2),
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

    public function test_posting_customer_payment_credits_ledger(): void
    {
        ['user' => $user, 'customer' => $customer] = $this->createFixtures();

        $sale = Sale::create([
            'invoice_number' => 'INV-00001',
            'warehouse_id' => Warehouse::first()->id,
            'customer_id' => $customer->id,
            'sale_date' => now(),
            'subtotal' => 200,
            'discount_amount' => 0,
            'total_amount' => 200,
            'amount_paid' => 0,
            'outstanding_amount' => 200,
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        $sale->items()->create([
            'product_id' => Product::first()->id,
            'quantity' => 20,
            'rate' => 10,
            'discount_amount' => 0,
            'line_total' => 200,
        ]);

        app(SalePostingService::class)->post($sale->fresh('items.product'), $user);

        $payment = CustomerPayment::create([
            'receipt_number' => 'RCP-00001',
            'customer_id' => $customer->id,
            'payment_date' => now(),
            'amount' => 50,
            'payment_method' => 'cash',
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        app(PaymentPostingService::class)->post($payment, $user);

        $balance = app(CustomerLedgerService::class)->getBalance($customer->id);

        $this->assertSame('posted', $payment->fresh()->status);
        $this->assertSame('150.00', $balance);
    }

    public function test_posting_sale_return_adds_stock_and_credits_ledger(): void
    {
        ['user' => $user, 'warehouse' => $warehouse, 'product' => $product, 'customer' => $customer] = $this->createFixtures();

        $saleReturn = SaleReturn::create([
            'return_number' => 'SRN-00001',
            'warehouse_id' => $warehouse->id,
            'customer_id' => $customer->id,
            'return_date' => now(),
            'subtotal' => 30,
            'total_amount' => 30,
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        $saleReturn->items()->create([
            'product_id' => $product->id,
            'quantity' => 3,
            'rate' => 10,
            'line_total' => 30,
        ]);

        app(SaleReturnPostingService::class)->post($saleReturn->fresh('items.product'), $user);

        $stock = ProductWarehouseStock::query()
            ->where('warehouse_id', $warehouse->id)
            ->where('product_id', $product->id)
            ->value('quantity');

        $balance = app(CustomerLedgerService::class)->getBalance($customer->id);

        $this->assertSame('posted', $saleReturn->fresh()->status);
        $this->assertSame('103.0000', (string) $stock);
        $this->assertSame('-30.00', $balance);
    }

    public function test_posting_stock_adjustment_updates_stock_in_and_out(): void
    {
        ['user' => $user, 'warehouse' => $warehouse, 'product' => $product] = $this->createFixtures(50);

        $adjustment = StockAdjustment::create([
            'adjustment_number' => 'ADJ-00001',
            'warehouse_id' => $warehouse->id,
            'adjustment_date' => now(),
            'adjustment_type' => 'damaged',
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        $adjustment->items()->create([
            'product_id' => $product->id,
            'direction' => 'out',
            'quantity' => 5,
        ]);

        $adjustment->items()->create([
            'product_id' => $product->id,
            'direction' => 'in',
            'quantity' => 2,
        ]);

        app(StockAdjustmentPostingService::class)->post($adjustment->fresh('items.product'), $user);

        $stock = ProductWarehouseStock::query()
            ->where('warehouse_id', $warehouse->id)
            ->where('product_id', $product->id)
            ->value('quantity');

        $this->assertSame('posted', $adjustment->fresh()->status);
        $this->assertSame('47.0000', (string) $stock);
    }
}
