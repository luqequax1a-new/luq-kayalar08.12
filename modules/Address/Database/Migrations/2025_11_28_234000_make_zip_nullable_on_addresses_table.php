<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('addresses') && Schema::hasColumn('addresses', 'zip')) {
            try {
                Schema::table('addresses', function (Blueprint $table) {
                    $table->string('zip')->nullable()->change();
                });
            } catch (\Throwable $e) {
                try {
                    DB::statement("ALTER TABLE addresses MODIFY zip VARCHAR(191) NULL");
                } catch (\Throwable $e2) {
                    // silently ignore; some drivers may not support change in dev
                }
            }
        }
    }

    public function down(): void
    {
        // No-op: keeping zip nullable is safe; revert only if needed
    }
};

