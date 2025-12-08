<?php

namespace Modules\DynamicCategory\Services;

use Modules\DynamicCategory\Entities\DynamicCategory;
use Modules\Product\Entities\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class DynamicCategoryProductService
{
    public function buildQuery(DynamicCategory $category): Builder
    {
        $includeTagSlugs = $category->includeTags()
            ->with('tag')
            ->get()
            ->map(function ($pivot) {
                return optional($pivot->tag)->slug;
            })
            ->filter()
            ->unique()
            ->values();

        Log::info('dynamic_category.build_query.tags', [
            'dynamic_category_id' => $category->id,
            'slug' => $category->slug,
            'include_tag_slugs' => $includeTagSlugs->all(),
        ]);

        // Base product query for storefront-visible products
        $query = Product::query()->forCard();

        if ($includeTagSlugs->isNotEmpty()) {
            $query->whereHas('tags', function ($q) use ($includeTagSlugs) {
                $q->whereIn('tags.slug', $includeTagSlugs);
            });
        } else {
            // If somehow no include tags, ensure empty result
            $query->whereRaw('1 = 0');
        }

        try {
            $productIds = (clone $query)->select('products.id')->pluck('products.id')->all();

            Log::info('dynamic_category.build_query.products', [
                'dynamic_category_id' => $category->id,
                'slug' => $category->slug,
                'product_ids' => $productIds,
                'count' => count($productIds),
            ]);
        } catch (\Throwable $e) {
            Log::error('dynamic_category.build_query.error', [
                'dynamic_category_id' => $category->id,
                'slug' => $category->slug,
                'message' => $e->getMessage(),
            ]);
        }

        return $query;
    }

    public function paginate(DynamicCategory $category, int $perPage = 24)
    {
        return $this->buildQuery($category)->paginate($perPage);
    }

    public function preview(DynamicCategory $category, int $limit = 20)
    {
        return $this->buildQuery($category)
            ->with('tags')
            ->limit($limit)
            ->get();
    }
}
