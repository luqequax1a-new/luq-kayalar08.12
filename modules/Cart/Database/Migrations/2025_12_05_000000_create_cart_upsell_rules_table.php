<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cart_upsell_rules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('status')->default(true);
            $table->string('trigger_type')->default('product_to_product');
            $table->unsignedInteger('main_product_id');
            $table->unsignedInteger('upsell_product_id');
            $table->unsignedInteger('preselected_variant_id')->nullable();
            $table->string('discount_type')->default('none'); // none, percent, fixed
            $table->decimal('discount_value', 18, 4)->default(0);
            $table->json('title')->nullable();
            $table->json('subtitle')->nullable();
            $table->string('internal_name')->nullable();
            $table->string('show_on')->default('checkout'); // checkout, post_checkout, product
            $table->decimal('min_cart_total', 18, 4)->nullable();
            $table->decimal('max_cart_total', 18, 4)->nullable();
            $table->boolean('hide_if_already_in_cart')->default(true);
            $table->boolean('has_countdown')->default(false);
            $table->unsignedInteger('countdown_minutes')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('main_product_id')
                ->references('id')->on('products')
                ->onDelete('cascade');

            $table->foreign('upsell_product_id')
                ->references('id')->on('products')
                ->onDelete('cascade');

            $table->foreign('preselected_variant_id')
                ->references('id')->on('product_variants')
                ->onDelete('set null');

            $table->index(['status', 'trigger_type', 'show_on', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_upsell_rules');
    }
};
