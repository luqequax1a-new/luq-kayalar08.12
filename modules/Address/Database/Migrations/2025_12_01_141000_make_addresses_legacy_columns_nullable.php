<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE addresses
            MODIFY address_1 VARCHAR(255) NULL,
            MODIFY address_2 VARCHAR(255) NULL,
            MODIFY city VARCHAR(255) NULL,
            MODIFY state VARCHAR(255) NULL,
            MODIFY zip VARCHAR(50) NULL,
            MODIFY invoice_title VARCHAR(255) NULL,
            MODIFY invoice_tax_office VARCHAR(255) NULL,
            MODIFY invoice_tax_number VARCHAR(50) NULL
        ");
    }

    public function down(): void
    {
        // No-op: keeping legacy columns nullable is safe
    }
};

