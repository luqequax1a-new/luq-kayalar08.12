<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Backfill primary_category_id for existing products that don't have it,
        // by picking the first attached category (by pivot position or id).
        try {
            \Modules\Product\Entities\Product::query()
                ->withoutGlobalScope('active')
                ->whereNull('primary_category_id')
                ->whereHas('categories')
                ->chunkById(200, function ($products) {
                    foreach ($products as $product) {
                        $first = $product->categories()->orderBy('position')->orderBy('categories.id')->first();
                        if ($first) {
                            $product->withoutEvents(function () use ($product, $first) {
                                $product->update(['primary_category_id' => $first->id]);
                            });
                        }
                    }
                });
        } catch (\Throwable $e) {
            // Migration should never hard fail the deployment because of backfill issues
        }
    }

    public function down(): void
    {
        // No rollback; primary_category_id backfill is safe to keep.
    }
};
