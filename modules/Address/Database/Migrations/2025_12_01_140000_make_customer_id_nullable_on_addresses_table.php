<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        try {
            DB::statement('ALTER TABLE addresses MODIFY customer_id BIGINT UNSIGNED NULL');
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
        try {
            DB::statement('ALTER TABLE addresses MODIFY customer_id BIGINT UNSIGNED NOT NULL');
        } catch (\Throwable $e) {
        }
    }
};

