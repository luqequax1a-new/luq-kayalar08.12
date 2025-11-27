<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('url_redirects')) return;

        Schema::table('url_redirects', function (Blueprint $table) {
            try {
                $table->dropUnique(['source_path']);
            } catch (\Throwable $e) {
            }

            try { $table->index('source_path'); } catch (\Throwable $e) {}

            try {
                $table->unique(['source_path', 'is_active']);
            } catch (\Throwable $e) {
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('url_redirects')) return;

        Schema::table('url_redirects', function (Blueprint $table) {
            try { $table->dropUnique(['source_path', 'is_active']); } catch (\Throwable $e) {}
            try { $table->dropIndex(['source_path']); } catch (\Throwable $e) {}
            try { $table->unique('source_path'); } catch (\Throwable $e) {}
        });
    }
};
