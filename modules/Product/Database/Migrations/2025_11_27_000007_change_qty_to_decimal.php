<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        \DB::table('products')->whereNull('qty')->update(['qty' => 0]);
        \DB::table('product_variants')->whereNull('qty')->update(['qty' => 0]);
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('qty', 10, 2)->default(0)->change();
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('qty', 10, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('qty')->default(0)->change();
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->integer('qty')->default(0)->change();
        });
    }
};
