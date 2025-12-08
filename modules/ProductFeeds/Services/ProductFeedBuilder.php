<?php

namespace Modules\ProductFeeds\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\Category\Entities\Category;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductVariant;

class ProductFeedBuilder
{
    public function queryProducts(array $options = [])
    {
        $includeOutOfStock = (bool) ($options['include_out_of_stock'] ?? setting('product_feeds.global.include_out_of_stock', false));
        $includeUnpublished = (bool) ($options['include_unpublished'] ?? setting('product_feeds.global.include_unpublished', false));

        $query = Product::query()
            ->with(['primaryCategory', 'categories', 'brand', 'productMedia', 'variants', 'variants.files'])
            ->with('translations');

        if (! $includeUnpublished) {
            $query->where('is_active', true);
        }

        if (! $includeOutOfStock) {
            $query->where(function ($q) {
                $q->where('in_stock', true)
                    ->where(function ($sq) {
                        $sq->where('manage_stock', false)->orWhere('qty', '>', 0);
                    });
            });
        }

        return $query;
    }

    public function buildCategoryPath($product): string
    {
        $category = $product->seoCategory();

        if (! $category) {
            return '';
        }

        $segments = [];
        $current = $category;

        while ($current) {
            $segments[] = $current->name;

            $parentId = $current->parent_id ?? null;

            if (! $parentId) {
                break;
            }

            $current = Category::query()->find($parentId);
        }

        return implode(' > ', array_reverse($segments));
    }

    public function productUrl(Product $product, $variantId = null): string
    {
        $url = $product->url();

        if ($variantId) {
            $separator = str_contains($url, '?') ? '&' : '?';
            return $url . $separator . 'variant=' . $variantId;
        }

        return $url;
    }

    public function brandName(Product $product): string
    {
        if ($product->relationLoaded('brand') && $product->brand) {
            return (string) $product->brand->name;
        }

        return (string) setting('product_feeds.global.brand_name', setting('store_name'));
    }

    public function availability(Product $product): string
    {
        return $product->is_out_of_stock ? 'out of stock' : 'in stock';
    }

    public function priceWithCurrency(Product $product): string
    {
        $currency = (string) setting('product_feeds.global.currency', currency());
        $amount = $product->selling_price ?? $product->price;

        $value = number_format((float) $amount, 2, '.', '');

        return $value . ' ' . $currency;
    }

    public function mainImage(Product $product): ?string
    {
        $baseImage = $product->base_image;

        return $baseImage['path'] ?? null;
    }

    public function additionalImages(Product $product): array
    {
        if (! $product->additional_images) {
            return [];
        }

        return $product->additional_images->pluck('path')->all();
    }

    protected function cleanText(?string $text): string
    {
        if ($text === null) {
            return '';
        }

        $clean = strip_tags($text);
        $clean = html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $clean = str_replace("\xC2\xA0", ' ', $clean);
        $clean = preg_replace('/\s+/u', ' ', $clean) ?? '';
        $clean = trim($clean);

        if ($clean === '') {
            return '';
        }

        return mb_convert_encoding($clean, 'UTF-8', 'UTF-8');
    }

    protected function buildDescription(Product $product): string
    {
        $meta = $product->seo_meta_description ?? null;

        if (is_string($meta) && $meta !== '') {
            return $meta;
        }

        $short = $this->cleanText($product->short_description ?? null);

        if ($short !== '') {
            return $short;
        }

        $desc = $this->cleanText($product->description ?? null);

        if ($desc !== '') {
            return Str::limit($desc, 500, '...');
        }

        return $this->cleanText($product->name ?? '');
    }


    protected function buildTitle(Product $product, ?ProductVariant $variant = null, string $channel = 'google'): string
    {
        $metaTitle = null;

        try {
            $metaTitle = optional($product->meta)->meta_title;
        } catch (\Throwable $e) {
            $metaTitle = null;
        }

        $base = $metaTitle ?: ($product->name ?? '');
        $base = $this->cleanText($base);

        if ($variant !== null && ! empty($variant->name)) {
            $variantName = $this->cleanText($variant->name);

            if ($variantName !== '') {
                $base = trim($base . ' - ' . $variantName);
            }
        }

        return Str::limit($base, 150, '');
    }

    protected function numericPriceForProduct(Product $product): array
    {
        $base = $product->price;
        $price = (float) $base->amount();

        $sale = null;

        if ($product->hasSpecialPrice()) {
            $sale = (float) $product->getSpecialPrice()->amount();
        }

        return [$price, $sale];
    }

    protected function numericPriceForVariant(ProductVariant $variant): array
    {
        $base = $variant->price;
        $price = (float) $base->amount();

        $sale = null;

        if ($variant->hasSpecialPrice()) {
            $sale = (float) $variant->getSpecialPrice()->amount();
        }

        return [$price, $sale];
    }

    /**
     * Build normalized feed items for a given channel.
     */
    public function normalizedItemsForFeed(string $channel): Collection
    {
        $includeVariantsGlobal = (bool) setting('product_feeds.global.include_variants', true);

        $includeVariants = match ($channel) {
            'meta' => (bool) setting('product_feeds.meta.use_variants', $includeVariantsGlobal),
            default => $includeVariantsGlobal,
        };

        $currency = (string) setting('product_feeds.global.currency', currency());
        $defaultGoogleCategory = (string) setting('product_feeds.google.category', '');

        $products = $this->queryProducts()->get();

        $rows = [];

        foreach ($products as $product) {
            $categoryPath = $this->buildCategoryPath($product);

            $productGoogleCategory = (string) ($product->google_product_category_path ?? '');

            if ($productGoogleCategory !== '') {
                $googleCategory = $productGoogleCategory;
            } elseif ($defaultGoogleCategory !== '') {
                $googleCategory = $defaultGoogleCategory;
            } else {
                $googleCategory = $categoryPath ?: null;
            }
            $brand = $this->brandName($product);
            $description = $this->buildDescription($product);

            $variants = $product->variants ?? collect();

            if ($includeVariants && $variants->count() > 0) {
                foreach ($variants as $variant) {
                    if ((bool) $variant->is_active === false) {
                        continue;
                    }

                    [$price, $sale] = $this->numericPriceForVariant($variant);

                    $mainImage = $variant->base_image?->path ?: $this->mainImage($product);
                    $additional = $variant->additional_images->pluck('path')->all();

                    $rows[] = [
                        'product' => $product,
                        'variant' => $variant,
                        'id' => (string) $variant->id,
                        'item_group_id' => (string) $product->id,
                        'sku' => $variant->sku ?: $product->sku,
                        'stock' => $variant->qty !== null ? (int) $variant->qty : null,
                        'availability' => $variant->is_out_of_stock ? 'out of stock' : 'in stock',
                        'title' => $this->buildTitle($product, $variant, $channel),
                        'description' => $description,
                        'url' => $variant->url(),
                        'brand' => $brand,
                        'category_path' => $categoryPath ?: null,
                        'product_type' => $categoryPath ?: '',
                        'google_category' => $googleCategory,
                        'price' => $price,
                        'sale_price' => $sale,
                        'currency' => $currency,
                        'main_image' => $mainImage,
                        'additional_images' => $additional,
                        'weight' => null,
                    ];
                }
            } else {
                [$price, $sale] = $this->numericPriceForProduct($product);

                $rows[] = [
                    'product' => $product,
                    'variant' => null,
                    'id' => (string) $product->id,
                    'item_group_id' => (string) $product->id,
                    'sku' => $product->sku,
                    'stock' => $product->qty !== null ? (int) $product->qty : null,
                    'availability' => $this->availability($product),
                    'title' => $this->buildTitle($product, null, $channel),
                    'description' => $description,
                    'url' => $this->productUrl($product),
                    'brand' => $brand,
                    'category_path' => $categoryPath ?: null,
                    'product_type' => $categoryPath ?: '',
                    'google_category' => $googleCategory,
                    'price' => $price,
                    'sale_price' => $sale,
                    'currency' => $currency,
                    'main_image' => $this->mainImage($product),
                    'additional_images' => $this->additionalImages($product),
                    'weight' => null,
                ];
            }
        }

        return collect($rows);
    }
}
