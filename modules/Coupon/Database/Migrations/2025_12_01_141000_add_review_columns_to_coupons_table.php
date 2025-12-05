<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            if (!Schema::hasColumn('coupons', 'is_review_coupon')) {
                $table->boolean('is_review_coupon')->default(false)->after('is_active');
            }
            if (!Schema::hasColumn('coupons', 'review_id')) {
                $table->unsignedInteger('review_id')->nullable()->after('is_review_coupon');
            }
            if (!Schema::hasColumn('coupons', 'order_id')) {
                $table->unsignedInteger('order_id')->nullable()->after('review_id');
            }
            if (!Schema::hasColumn('coupons', 'customer_id')) {
                $table->unsignedInteger('customer_id')->nullable()->after('order_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            foreach (['is_review_coupon', 'review_id', 'order_id', 'customer_id'] as $col) {
                if (Schema::hasColumn('coupons', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

