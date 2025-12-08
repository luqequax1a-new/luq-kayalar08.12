<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('popups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->boolean('status')->default(true);
            $table->string('device')->default('both');
            $table->string('trigger_type')->default('on_load_delay');
            $table->integer('trigger_value')->nullable();
            $table->string('frequency_type')->default('per_session');
            $table->integer('frequency_value')->nullable();
            $table->string('target_scope')->default('all');
            $table->json('targeting')->nullable();
            $table->string('headline')->nullable();
            $table->string('subheadline')->nullable();
            $table->text('body')->nullable();
            $table->string('cta_label')->nullable();
            $table->string('cta_url')->nullable();
            $table->string('close_label')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamps();

            $table->index(['status', 'target_scope', 'device']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('popups');
    }
};
