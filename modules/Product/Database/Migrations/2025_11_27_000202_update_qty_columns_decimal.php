<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        // products.qty -> decimal(18,2)
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                try {
                    $table->decimal('qty', 18, 2)->nullable()->change();
                } catch (\Throwable $e) {
                }
            });
        }

        // product_variants.qty -> decimal(18,2)
        if (Schema::hasTable('product_variants')) {
            Schema::table('product_variants', function (Blueprint $table) {
                try {
                    $table->decimal('qty', 18, 2)->nullable()->change();
                } catch (\Throwable $e) {
                }
            });
        }
    }

    public function down(): void
    {
        // revert to integer (precision loss possible)
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                try {
                    $table->integer('qty')->nullable()->change();
                } catch (\Throwable $e) {
                }
            });
        }

        if (Schema::hasTable('product_variants')) {
            Schema::table('product_variants', function (Blueprint $table) {
                try {
                    $table->integer('qty')->nullable()->change();
                } catch (\Throwable $e) {
                }
            });
        }
    }
};

