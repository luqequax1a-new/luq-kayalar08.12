<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'created_from')) {
                $table->enum('created_from', ['storefront', 'admin_manual'])->default('storefront');
            }
            if (!Schema::hasColumn('orders', 'created_by_admin_id')) {
                $table->unsignedBigInteger('created_by_admin_id')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'created_from')) {
                $table->dropColumn('created_from');
            }
            if (Schema::hasColumn('orders', 'created_by_admin_id')) {
                $table->dropColumn('created_by_admin_id');
            }
        });
    }
};

