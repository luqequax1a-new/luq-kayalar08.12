<?php

namespace Modules\Product\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\Product\Entities\Product;

class ProductDuplicator
{
    public function duplicate(Product $product): Product
    {
        return DB::transaction(function () use ($product) {
            $now = now();
            $product->loadMissing(['categories', 'tags', 'translations']);

            $copy = $product->replicate();

            $copy->slug = $this->generateUniqueSlug($product->slug);
            $copy->sku = $this->generateNewSku($product->sku);
            $copy->is_active = false;
            $copy->viewed = 0;
            $copy->selling_price = null;
            $copy->created_at = $now;
            $copy->updated_at = $now;

            $copy->save();

            $translations = $product->translations;
            foreach ($translations as $translation) {
                $t = $translation->replicate();
                $t->product_id = $copy->id;
                if (Schema::hasColumn('product_translations', 'slug')) {
                    $base = $translation->slug ?? Str::slug($translation->name);
                    $t->slug = $this->generateUniqueTranslationSlug($base);
                }
                if (Schema::hasColumn('product_translations', 'name')) {
                    $t->name = 'Copy ' . (string) $translation->name;
                }
                $t->save();
            }

            $categoryIds = $product->categories->pluck('id')->all();
            $tagIds = $product->tags->pluck('id')->all();

            if (!empty($categoryIds)) {
                $copy->categories()->sync($categoryIds);
            }
            if (!empty($tagIds)) {
                $copy->tags()->sync($tagIds);
            }

            return $copy;
        });
    }

    public function generateNewSku(?string $oldSku): string
    {
        $prefix = $oldSku ? trim($oldSku) : 'SKU';

        do {
            $rand = strtoupper(Str::random(5));
            $candidate = $prefix . '-' . $rand;
        } while (Product::query()->where('sku', $candidate)->exists());

        return $candidate;
    }

    public function generateUniqueSlug(string $baseSlug): string
    {
        $candidate = $baseSlug . '-copy';
        $i = 1;

        $exists = function ($slug) {
            if (Product::query()->where('slug', $slug)->exists()) {
                return true;
            }
            if (Schema::hasColumn('product_translations', 'slug')) {
                if (DB::table('product_translations')->where('slug', $slug)->exists()) {
                    return true;
                }
            }
            return false;
        };

        while ($exists($candidate)) {
            $candidate = $baseSlug . '-copy-' . $i;
            $i++;
        }

        return $candidate;
    }

    public function generateUniqueTranslationSlug(string $baseSlug): string
    {
        $candidate = $baseSlug . '-copy';
        $i = 1;

        while (DB::table('product_translations')->where('slug', $candidate)->exists()) {
            $candidate = $baseSlug . '-copy-' . $i;
            $i++;
        }

        return $candidate;
    }
}
