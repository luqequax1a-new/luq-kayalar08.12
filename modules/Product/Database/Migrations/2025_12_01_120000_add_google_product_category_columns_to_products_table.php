<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('google_product_category_id')->nullable()->after('primary_category_id');
            $table->string('google_product_category_path')->nullable()->after('google_product_category_id');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['google_product_category_id', 'google_product_category_path']);
        });
    }
};

