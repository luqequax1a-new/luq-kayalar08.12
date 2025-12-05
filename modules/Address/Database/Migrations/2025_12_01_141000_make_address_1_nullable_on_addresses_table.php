<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        try {
            DB::statement('ALTER TABLE addresses MODIFY address_1 VARCHAR(255) NULL');
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
        try {
            DB::statement('ALTER TABLE addresses MODIFY address_1 VARCHAR(255) NOT NULL');
        } catch (\Throwable $e) {
        }
    }
};

