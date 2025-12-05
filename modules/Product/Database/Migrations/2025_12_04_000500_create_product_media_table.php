<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('product_media')) {
            Schema::create('product_media', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('product_id');
                $table->unsignedInteger('variant_id')->nullable();
                $table->string('type');
                $table->string('path');
                $table->string('poster')->nullable();
                $table->integer('position')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        try {
            DB::statement('ALTER TABLE `product_media` ADD INDEX `product_media_product_id_index` (`product_id`)');
        } catch (\Throwable $e) {}
        try {
            DB::statement('ALTER TABLE `product_media` ADD INDEX `product_media_variant_id_index` (`variant_id`)');
        } catch (\Throwable $e) {}

        try {
            DB::statement('ALTER TABLE `product_media` ADD CONSTRAINT `product_media_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE');
        } catch (\Throwable $e) {}
        try {
            DB::statement('ALTER TABLE `product_media` ADD CONSTRAINT `product_media_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE CASCADE');
        } catch (\Throwable $e) {}
    }

    public function down(): void
    {
        Schema::dropIfExists('product_media');
    }
};

