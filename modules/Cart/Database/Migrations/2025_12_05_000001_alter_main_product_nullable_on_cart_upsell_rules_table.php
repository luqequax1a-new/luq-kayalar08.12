<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cart_upsell_rules', function (Blueprint $table) {
            // main_product_id yalnızca trigger_type = product_to_product iken anlamlı,
            // bu yüzden ALL_PRODUCTS senaryosu için nullable yapıyoruz.
            $table->unsignedInteger('main_product_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('cart_upsell_rules', function (Blueprint $table) {
            $table->unsignedInteger('main_product_id')->nullable(false)->change();
        });
    }
};
