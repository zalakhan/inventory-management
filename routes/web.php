<?php

use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Reports\ReportController;
use App\Http\Controllers\Inventory\InventoryController;
use App\Http\Controllers\Inventory\StockAdjustmentController;
use App\Http\Controllers\Inventory\StockReceivingController;
use App\Http\Controllers\Master\AreaController;
use App\Http\Controllers\Master\CustomerController;
use App\Http\Controllers\Master\ProductCategoryController;
use App\Http\Controllers\Master\ProductController;
use App\Http\Controllers\Master\SalesmanController;
use App\Http\Controllers\Master\SupplierController;
use App\Http\Controllers\Master\WarehouseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Sales\CustomerLedgerController;
use App\Http\Controllers\Sales\CustomerPaymentController;
use App\Http\Controllers\Sales\SaleController;
use App\Http\Controllers\Sales\SaleReturnController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'permission:dashboard.view'])
    ->name('dashboard');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');

        Route::get('users/{user}/permissions', [UserController::class, 'editPermissions'])->name('users.permissions.edit');
        Route::put('users/{user}/permissions', [UserController::class, 'updatePermissions'])->name('users.permissions.update');
        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class)->except(['show']);
        Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
    });

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('stock-summary', [ReportController::class, 'stockSummary'])->name('stock_summary');
        Route::get('sales-register', [ReportController::class, 'salesRegister'])->name('sales_register');
        Route::get('customer-outstanding', [ReportController::class, 'customerOutstanding'])->name('customer_outstanding');
        Route::get('stock-movements', [ReportController::class, 'stockMovements'])->name('stock_movements');
        Route::get('payment-register', [ReportController::class, 'paymentRegister'])->name('payment_register');
    });

    Route::prefix('master')->name('master.')->group(function () {
        Route::resource('warehouses', WarehouseController::class)->except(['show']);
        Route::resource('categories', ProductCategoryController::class)
            ->parameters(['categories' => 'product_category'])
            ->except(['show']);
        Route::resource('products', ProductController::class)->except(['show']);
        Route::resource('customers', CustomerController::class)->except(['show']);
        Route::resource('suppliers', SupplierController::class)->except(['show']);
        Route::resource('areas', AreaController::class)->except(['show']);
        Route::resource('salesmen', SalesmanController::class)->except(['show']);
    });

    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('stock', [InventoryController::class, 'index'])->name('stock.index');
        Route::get('movements', [InventoryController::class, 'movements'])->name('movements.index');

        Route::post('stock-receiving/{stock_receiving}/post', [StockReceivingController::class, 'post'])
            ->name('stock_receiving.post');
        Route::post('stock-receiving/{stock_receiving}/cancel', [StockReceivingController::class, 'cancel'])
            ->name('stock_receiving.cancel');
        Route::resource('stock-receiving', StockReceivingController::class)->names([
            'index' => 'stock_receiving.index',
            'create' => 'stock_receiving.create',
            'store' => 'stock_receiving.store',
            'show' => 'stock_receiving.show',
            'edit' => 'stock_receiving.edit',
            'update' => 'stock_receiving.update',
            'destroy' => 'stock_receiving.destroy',
        ]);

        Route::post('stock-adjustments/{stock_adjustment}/post', [StockAdjustmentController::class, 'post'])
            ->name('stock_adjustments.post');
        Route::post('stock-adjustments/{stock_adjustment}/cancel', [StockAdjustmentController::class, 'cancel'])
            ->name('stock_adjustments.cancel');
        Route::resource('stock-adjustments', StockAdjustmentController::class)->names([
            'index' => 'stock_adjustments.index',
            'create' => 'stock_adjustments.create',
            'store' => 'stock_adjustments.store',
            'show' => 'stock_adjustments.show',
            'edit' => 'stock_adjustments.edit',
            'update' => 'stock_adjustments.update',
            'destroy' => 'stock_adjustments.destroy',
        ]);
    });

    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('customer-ledger', [CustomerLedgerController::class, 'index'])->name('customer_ledger.index');
        Route::get('customer-ledger/{customer}', [CustomerLedgerController::class, 'show'])->name('customer_ledger.show');

        Route::post('invoices/{invoice}/post', [SaleController::class, 'post'])->name('invoices.post');
        Route::post('invoices/{invoice}/cancel', [SaleController::class, 'cancel'])->name('invoices.cancel');
        Route::resource('invoices', SaleController::class)->names([
            'index' => 'invoices.index',
            'create' => 'invoices.create',
            'store' => 'invoices.store',
            'show' => 'invoices.show',
            'edit' => 'invoices.edit',
            'update' => 'invoices.update',
            'destroy' => 'invoices.destroy',
        ]);

        Route::post('payments/{payment}/post', [CustomerPaymentController::class, 'post'])
            ->name('payments.post');
        Route::post('payments/{payment}/cancel', [CustomerPaymentController::class, 'cancel'])
            ->name('payments.cancel');
        Route::resource('payments', CustomerPaymentController::class)->names([
            'index' => 'payments.index',
            'create' => 'payments.create',
            'store' => 'payments.store',
            'show' => 'payments.show',
            'edit' => 'payments.edit',
            'update' => 'payments.update',
            'destroy' => 'payments.destroy',
        ]);

        Route::post('returns/{sale_return}/post', [SaleReturnController::class, 'post'])
            ->name('returns.post');
        Route::post('returns/{sale_return}/cancel', [SaleReturnController::class, 'cancel'])
            ->name('returns.cancel');
        Route::resource('returns', SaleReturnController::class)
            ->parameters(['returns' => 'sale_return'])
            ->names([
                'index' => 'returns.index',
                'create' => 'returns.create',
                'store' => 'returns.store',
                'show' => 'returns.show',
                'edit' => 'returns.edit',
                'update' => 'returns.update',
                'destroy' => 'returns.destroy',
            ]);
    });
});

require __DIR__.'/auth.php';
