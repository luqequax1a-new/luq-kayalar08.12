<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {
    public function up(): void
    {
        try {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('qty', 10, 2)->default(0)->change();
            });
        } catch (\Throwable $e) {
            // ignore decimal conversion warnings
        }

        try {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->decimal('qty', 10, 2)->default(0)->change();
            });
        } catch (\Throwable $e) {
            // ignore decimal conversion warnings
        }
    }

    public function down(): void
    {
        try {
            Schema::table('products', function (Blueprint $table) {
                $table->integer('qty')->default(0)->change();
            });
        } catch (\Throwable $e) {
            // ignore
        }

        try {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->integer('qty')->default(0)->change();
            });
        } catch (\Throwable $e) {
            // ignore
        }
    }
};
