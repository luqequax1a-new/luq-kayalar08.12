<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('seo_bulk_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('job_id');
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->string('field');
            $table->longText('old_value')->nullable();
            $table->longText('new_value')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->boolean('dry_run')->default(false);
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_bulk_logs');
    }
};
