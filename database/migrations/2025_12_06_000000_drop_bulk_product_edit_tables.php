<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('product_bulk_edit_logs');
        Schema::dropIfExists('product_bulk_edit_runs');
        Schema::dropIfExists('product_bulk_edit_actions');
        Schema::dropIfExists('product_bulk_edit_filters');
        Schema::dropIfExists('product_bulk_edits');

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Intentionally left empty; tables will not be recreated.
    }
};
