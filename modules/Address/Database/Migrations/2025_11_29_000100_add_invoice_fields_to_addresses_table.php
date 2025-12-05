<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->string('invoice_title')->nullable();
            $table->string('invoice_tax_number', 50)->nullable();
            $table->string('invoice_tax_office')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn(['invoice_title', 'invoice_tax_number', 'invoice_tax_office']);
        });
    }
};

