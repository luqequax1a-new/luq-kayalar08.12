<?php

namespace Modules\Storefront\Http\ViewComposers;

use Illuminate\View\View;
use Spatie\SchemaOrg\Schema;
use Modules\Storefront\Banner;
use Modules\Storefront\Feature;
use Illuminate\Support\Collection;
use Modules\Product\Entities\Product;
use Spatie\SchemaOrg\ItemAvailability;

class ProductShowPageComposer
{
    /**
     * Bind data to the view.
     *
     * @param View $view
     *
     * @return void
     */
    public function compose(View $view)
    {
        $product = $view->getData()['product'];

        $view->with([
            'features' => Feature::all(),
            'banner' => Banner::getProductPageBanner(),
            'productSchemaMarkup' => $this->schemaMarkup($product),
            'categoryBreadcrumb' => $this->getSeoCategoryBreadcrumb($product),
            'breadcrumbSchemaMarkup' => $this->breadcrumbSchema($product),
        ]);
    }


    private function schemaMarkup(Product $product)
    {
        $imagePath = null;
        try {
            $imagePath = optional(optional($product->variant)->base_image)->path
                ?: optional($product->base_image)->path
                ?: asset('build/assets/image-placeholder.png');
        } catch (\Throwable $e) {
            $imagePath = asset('build/assets/image-placeholder.png');
        }

        $schema = Schema::product()
            ->name($product->name)
            ->sku($product->sku)
            ->url(url($product->url()))
            ->image(url($imagePath))
            ->brand($this->brandSchema($product))
            ->description($product->short_description);

        $offers = $this->offersSchema($product);
        if ($offers) {
            $schema->offers($offers);
        }

        try {
            $categoryPath = $this->buildCategoryPath($product);
            if ($categoryPath) {
                $schema->setProperty('category', $categoryPath);
            }

            if ($product->google_product_category_path) {
                $schema->setProperty('googleProductCategory', $product->google_product_category_path);
            }

            $schema->setProperty('productId', (string) $product->id);

            $mpn = $product->sku ?: ($product->id ? (string) $product->id : null);
            if ($mpn) {
                $schema->setProperty('mpn', $mpn);
            }

            $schema->setProperty('itemCondition', 'https://schema.org/NewCondition');
        } catch (\Throwable $e) {
        }

        if ($product->reviews()->count() > 0) {
            $schema->aggregateRating($this->aggregateRatingSchema($product));
        }

        return $schema;
    }


    private function brandSchema(Product $product)
    {
        $brandName = null;
        try {
            $brandName = optional($product->brand)->name;
        } catch (\Throwable $e) {
        }
        if (!$brandName || strtolower($brandName) === 'brand') {
            $brandName = config('app.name') ?: 'Store';
        }
        return Schema::brand()->name($brandName);
    }


    private function aggregateRatingSchema(Product $product)
    {
        return Schema::aggregateRating()
            ->ratingValue($product->reviews()->avg('rating'))
            ->ratingCount($product->reviews()->count());
    }


    private function offersSchema(Product $product)
    {
        $format = function ($money) {
            try {
                $amount = $money->convertToCurrentCurrency()->amount();
                return number_format((float) $amount, 2, '.', '');
            } catch (\Throwable $e) {
                return null;
            }
        };

        try {
            $variants = $product->variants()->withoutGlobalScope('active')->get();
            $valid = $variants->filter(function ($v) {
                try {
                    $price = optional($v->selling_price)->convertToCurrentCurrency()->amount();
                    return (bool) $v->is_active && (bool) $v->isInStock() && $price !== null && $price > 0;
                } catch (\Throwable $e) {
                    return false;
                }
            });

            if ($valid->isEmpty()) {
                $base = ($product->variant ?? $product);
                $priceStr = $format($base->selling_price);
                if (!$priceStr) {
                    return null;
                }

                $offer = Schema::offer()
                    ->price($priceStr)
                    ->priceCurrency(currency())
                    ->availability($product->isInStock() ? ItemAvailability::InStock : ItemAvailability::OutOfStock)
                    ->url(url($product->url()))
                    ->setProperty('itemCondition', 'https://schema.org/NewCondition');

                return $this->attachPriceValidUntil($offer, $product);
            }

            if ($valid->count() === 1) {
                $v = $valid->first();
                $priceStr = $format($v->selling_price);
                $offer = Schema::offer()
                    ->price($priceStr)
                    ->priceCurrency(currency())
                    ->availability($v->isInStock() ? ItemAvailability::InStock : ItemAvailability::OutOfStock)
                    ->url(url($product->url()))
                    ->setProperty('itemCondition', 'https://schema.org/NewCondition');

                return $this->attachPriceValidUntil($offer, $product);
            }

            $prices = $valid->map(function ($v) use ($format) {
                return $format($v->selling_price);
            })->filter(function ($p) { return is_string($p) && $p !== '' && (float) $p > 0; });

            $low = $prices->map('floatval')->min();
            $high = $prices->map('floatval')->max();
            $offerCount = $valid->count();

            return Schema::aggregateOffer()
                ->lowPrice(number_format((float) $low, 2, '.', ''))
                ->highPrice(number_format((float) $high, 2, '.', ''))
                ->offerCount($offerCount)
                ->priceCurrency(currency())
                ->url(url($product->url()));
        } catch (\Throwable $e) {
            $base = ($product->variant ?? $product);
            $priceStr = $format($base->selling_price);
            if (!$priceStr) {
                return null;
            }
            $offer = Schema::offer()
                ->price($priceStr)
                ->priceCurrency(currency())
                ->availability($product->isInStock() ? ItemAvailability::InStock : ItemAvailability::OutOfStock)
                ->url(url($product->url()))
                ->setProperty('itemCondition', 'https://schema.org/NewCondition');

            return $this->attachPriceValidUntil($offer, $product);
        }
    }


    private function attachPriceValidUntil($offer, Product $product)
    {
        try {
            if ($product->special_price && $product->special_price_end) {
                // special_price_end is casted to datetime (Carbon), ensure it's a future date
                $end = $product->special_price_end;

                if (method_exists($end, 'isFuture') && $end->isFuture()) {
                    // Google is fine with a simple YYYY-MM-DD date
                    $offer->priceValidUntil($end->toDateString());
                }
            }
        } catch (\Throwable $e) {
            // Fail silently; priceValidUntil is optional
        }

        return $offer;
    }

    private function buildCategoryPath(Product $product): ?string
    {
        try {
            $category = $product->seoCategory();
            if (!$category) {
                return null;
            }

            $trail = collect();
            while ($category) {
                $trail->prepend($category);
                $category = $category->parent_id ? \Modules\Category\Entities\Category::withoutGlobalScope('active')->find($category->parent_id) : null;
            }
            $names = $trail->map(function ($cat) { return (string) $cat->name; })->all();
            return implode(' > ', $names);
        } catch (\Throwable $e) {
            return null;
        }
    }


    private function getSeoCategoryBreadcrumb(Product $product)
    {
        $category = $product->seoCategory();

        if (!$category) {
            return '';
        }

        $trail = collect();

        while ($category) {
            $trail->prepend($category);
            $category = $category->parent_id ? \Modules\Category\Entities\Category::withoutGlobalScope('active')->find($category->parent_id) : null;
        }

        return $trail->map(function ($cat) {
            return "<li><a href='" . $cat->url() . "'>" . e($cat->name) . "</a></li>";
        })->implode('');
    }


    private function breadcrumbSchema(Product $product)
    {
        $items = [];

        $position = 1;
        $items[] = Schema::listItem()
            ->position($position++)
            ->item(
                Schema::thing()
                    ->name(trans('storefront::layouts.home'))
                    ->id(route('home'))
            );

        $category = $product->seoCategory();

        if ($category) {
            $trail = collect();

            while ($category) {
                $trail->prepend($category);
                $category = $category->parent_id ? \Modules\Category\Entities\Category::withoutGlobalScope('active')->find($category->parent_id) : null;
            }

            foreach ($trail as $cat) {
                $items[] = Schema::listItem()
                    ->position($position++)
                    ->item(
                        Schema::thing()
                            ->name($cat->name)
                            ->id($cat->url())
                    );
            }
        }

        $items[] = Schema::listItem()
            ->position($position++)
            ->item(
                Schema::thing()
                    ->name($product->name)
                    ->id($product->url())
            );

        return Schema::breadcrumbList()->itemListElement($items);
    }
}
