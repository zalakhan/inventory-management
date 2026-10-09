<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_payments', function (Blueprint $table) {
            $table->text('cancellation_reason')->nullable()->after('cancelled_at');
        });

        Schema::table('sale_returns', function (Blueprint $table) {
            $table->text('cancellation_reason')->nullable()->after('cancelled_at');
        });

        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->text('cancellation_reason')->nullable()->after('cancelled_at');
        });
    }

    public function down(): void
    {
        Schema::table('customer_payments', function (Blueprint $table) {
            $table->dropColumn('cancellation_reason');
        });

        Schema::table('sale_returns', function (Blueprint $table) {
            $table->dropColumn('cancellation_reason');
        });

        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->dropColumn('cancellation_reason');
        });
    }
};
