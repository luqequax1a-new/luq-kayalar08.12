<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('order_products', function (Blueprint $table) {
            if (!Schema::hasColumn('order_products', 'product_name')) {
                $table->string('product_name')->nullable();
            }
            if (!Schema::hasColumn('order_products', 'product_slug')) {
                $table->string('product_slug')->nullable();
            }
            if (!Schema::hasColumn('order_products', 'product_sku')) {
                $table->string('product_sku')->nullable();
            }
            if (!Schema::hasColumn('order_products', 'unit_price_at_order')) {
                $table->decimal('unit_price_at_order', 18, 4)->unsigned()->nullable();
            }
            if (!Schema::hasColumn('order_products', 'unit_label')) {
                $table->string('unit_label')->nullable();
            }
            if (!Schema::hasColumn('order_products', 'unit_short_suffix')) {
                $table->string('unit_short_suffix')->nullable();
            }
        });

        Schema::table('order_products', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->integer('product_id')->unsigned()->nullable()->change();
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_products', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->integer('product_id')->unsigned()->nullable(false)->change();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            if (Schema::hasColumn('order_products', 'product_name')) {
                $table->dropColumn('product_name');
            }
            if (Schema::hasColumn('order_products', 'product_slug')) {
                $table->dropColumn('product_slug');
            }
            if (Schema::hasColumn('order_products', 'product_sku')) {
                $table->dropColumn('product_sku');
            }
            if (Schema::hasColumn('order_products', 'unit_price_at_order')) {
                $table->dropColumn('unit_price_at_order');
            }
            if (Schema::hasColumn('order_products', 'unit_label')) {
                $table->dropColumn('unit_label');
            }
            if (Schema::hasColumn('order_products', 'unit_short_suffix')) {
                $table->dropColumn('unit_short_suffix');
            }
        });
    }
};
