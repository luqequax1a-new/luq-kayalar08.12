<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dynamic_category_rules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('dynamic_category_id');
            $table->unsignedInteger('group_no')->default(1);
            $table->string('field');
            $table->string('operator');
            $table->text('value')->nullable();
            $table->string('boolean', 3)->default('AND');
            $table->timestamps();

            $table->foreign('dynamic_category_id')
                ->references('id')
                ->on('dynamic_categories')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dynamic_category_rules');
    }
};
