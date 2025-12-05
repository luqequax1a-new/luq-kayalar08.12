<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tag_badges', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('name');
            $table->string('slug')->unique();

            $table->unsignedInteger('tag_id');

            $table->string('image_path')->nullable();

            $table->boolean('is_active')->default(true);

            $table->boolean('show_on_listing')->default(true);
            $table->enum('listing_position', ['top_left', 'top_right', 'bottom_left', 'bottom_right'])
                  ->default('top_left');

            $table->boolean('show_on_detail')->default(true);
            $table->enum('detail_position', ['top_left', 'top_right', 'bottom_left', 'bottom_right'])
                  ->default('top_left');

            $table->integer('priority')->default(0);

            $table->timestamps();

            $table->foreign('tag_id')
                ->references('id')
                ->on('tags')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tag_badges');
    }
};
