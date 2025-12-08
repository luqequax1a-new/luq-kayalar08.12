<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dynamic_category_tag', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('dynamic_category_id');
            $table->unsignedInteger('tag_id');
            $table->enum('type', ['include', 'exclude']);
            $table->timestamps();

            $table->foreign('dynamic_category_id')
                ->references('id')
                ->on('dynamic_categories')
                ->onDelete('cascade');

            $table->foreign('tag_id')
                ->references('id')
                ->on('tags')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dynamic_category_tag');
    }
};
