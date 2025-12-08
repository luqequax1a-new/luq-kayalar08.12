<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
 
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use Modules\Category\Entities\Category;
use Modules\Attribute\Entities\Attribute;
use Modules\Product\Filters\ProductFilter;
use Modules\Product\Events\ShowingProductList;

trait ProductSearch
{
    /**
     * Search products for the request.
     *
     * @param Product $model
     * @param ProductFilter $productFilter
     *
     * @return JsonResponse
     */
    public function searchProducts(Product $model, ProductFilter $productFilter)
    {
        $productIds = [];

        if (request()->filled('query')) {
            $search = $model->search(request('query'));
            $productIds = $search->keys();

            if ($productIds->isEmpty()) {
                $query = $productFilter->apply(
                    $model->newQuery()->whereTranslationLike('name', '%' . request('query') . '%')
                )->forCard();
            } else {
                $query = $search->filter($productFilter)->forCard();
            }
        } else {
            $query = $model->filter($productFilter)->forCard();
        }

        if (request()->filled('category')) {
            $productIds = (clone $query)->select('products.id')->resetOrders()->pluck('id');
        }

        {
            $perPage = (int) request('perPage', 30);
            $page = max(1, (int) request('page', 1));

            $all = (clone $query)->get();
            $all->load([
                'variants' => function ($q) {
                    $q->orderBy('position');
                },
                'variations',
                'tags',
                'tags.tagBadges' => function ($q) {
                    $q->active();
                },
            ]);

            $items = $all->flatMap(function (Product $product) {
                $tagBadges = $product->badgeVisualsFor('listing')->map(function ($badge) {
                    return [
                        'name' => $badge->name,
                        'image_url' => $badge->image_url,
                        'listing_position' => $badge->listing_position,
                        'detail_position' => $badge->detail_position,
                        'priority' => $badge->priority,
                    ];
                })->values();
                $variantLabel = optional($product->variations->first())->name;
                if ($product->list_variants_separately) {
                    $variants = $product->variants()->orderBy('position')->get();
                    $actives = $variants->filter(function ($v) {
                        return (bool) ($v->is_active ?? false);
                    });

                    if ($actives->isNotEmpty()) {
                        return $actives->map(function ($variant) use ($product, $tagBadges, $variantLabel) {
                            $p = $product->clean();
                            $p['variant_attribute_label'] = $variantLabel;
                            $p['name'] = $product->name;
                            $p['variant'] = $variant->toArray();
                            $p['url'] = $variant->url() ?? $product->url();
                            $p['base_image'] = ($variant->base_image ?? $product->base_image);
                            $p['base_image_thumb'] = [
                                'path' => media_variant_url(($variant->base_image ?? $product->base_image), 400)
                            ];
                            $p['variant']['base_image_thumb'] = [
                                'path' => media_variant_url(($variant->base_image ?? $product->base_image), 80)
                            ];
                            $p['formatted_price'] = $variant->formatted_price ?? $product->formatted_price;
                            $p['formatted_price_range'] = null;
                            $p['reviews_count'] = $product->reviews_count ?? ($product->relationLoaded('reviews') ? $product->reviews->count() : 0);
                            $p['rating_percent'] = $product->rating_percent;
                            $p['tag_badges'] = $tagBadges;
                            return $p;
                        });
                    }
                }

                $base = $product->clean();
                $base['variant_attribute_label'] = $variantLabel;
                $base['reviews_count'] = $product->reviews_count ?? ($product->relationLoaded('reviews') ? $product->reviews->count() : 0);
                $base['rating_percent'] = $product->rating_percent;
                $base['base_image_thumb'] = [
                    'path' => media_variant_url($product->base_image, 400)
                ];
                $base['tag_badges'] = $tagBadges;

                return collect([$base]);
            })->values();

            $total = $items->count();
            $sliced = $items->forPage($page, $perPage)->values();
            $paginator = new LengthAwarePaginator(
                $sliced,
                $total,
                $perPage,
                $page,
                [
                    'path' => request()->url(),
                    'query' => request()->query(),
                ]
            );

            event(new ShowingProductList($paginator));

            $categoryData = [
                'name' => null,
                'slug' => null,
                'description_html' => '',
                'faq_items' => [],
            ];

            if (request()->filled('category')) {
                $category = Category::where('slug', request('category'))->first();

                if ($category && $category->exists) {
                    $faqItems = is_array($category->faq_items) ? $category->faq_items : [];

                    $categoryData['name'] = $category->name;
                    $categoryData['slug'] = $category->slug;
                    $categoryData['description_html'] = $category->description ?? '';
                    $categoryData['faq_items'] = $faqItems;
                }
            }

            return response()->json([
                'products' => $paginator,
                'attributes' => $this->getAttributes($productIds),
                'category' => $categoryData,
            ]);
        }
    }


    private function getAttributes($productIds)
    {
        if (!request()->filled('category') || $this->filteringViaRootCategory()) {
            return collect();
        }

        return Attribute::with('values')
            ->where('is_filterable', true)
            ->whereHas('categories', function ($query) use ($productIds) {
                $query->whereIn('id', $this->getProductsCategoryIds($productIds));
            })
            ->get();
    }


    private function filteringViaRootCategory()
    {
        return Category::where('slug', request('category'))
            ->firstOrNew([])
            ->isRoot();
    }


    private function getProductsCategoryIds($productIds)
    {
        return DB::table('product_categories')
            ->whereIn('product_id', $productIds)
            ->distinct()
            ->pluck('category_id');
    }


    private function buildFaqHtml(array $faqList): string
    {
        if (empty($faqList)) {
            return '';
        }

        $html = '';

        foreach ($faqList as $item) {
            $q = isset($item['q']) ? e($item['q']) : '';
            $a = isset($item['a']) ? (string) $item['a'] : '';

            if ($q === '' && trim($a) === '') {
                continue;
            }

            $html .= '<div class="faq-item">';

            if ($q !== '') {
                $html .= '<h3 class="faq-question">' . $q . '</h3>';
            }

            if (trim($a) !== '') {
                $html .= '<div class="faq-answer">' . $a . '</div>';
            }

            $html .= '</div>';
        }

        return $html;
    }
}
