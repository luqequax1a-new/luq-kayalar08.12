<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('order_products', function (Blueprint $table) {
            $table->string('unit_code')->nullable();
            $table->string('unit_label')->nullable();
            $table->string('unit_short_suffix')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('order_products', function (Blueprint $table) {
            $table->dropColumn(['unit_code', 'unit_label', 'unit_short_suffix']);
        });
    }
};
