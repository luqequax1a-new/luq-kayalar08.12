<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\FlashSale\Entities\FlashSale;
use Modules\Product\Entities\ProductVariant;
use Modules\Review\Entities\Review;
use Illuminate\Contracts\View\View;
use Modules\Product\Entities\Product;
use Illuminate\Contracts\View\Factory;
use Modules\Product\Events\ProductViewed;
use Modules\Product\Filters\ProductFilter;
use Illuminate\Contracts\Foundation\Application;
use Modules\Product\Repositories\ProductRepository;
use Modules\Product\Http\Middleware\SetProductSortOption;
use Modules\Product\Entities\UrlRedirect;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Illuminate\Support\Str;
use Modules\Category\Entities\Category;
use Modules\DynamicCategory\Entities\DynamicCategory;
use Modules\DynamicCategory\Services\DynamicCategoryProductService;
use Modules\Product\Events\ShowingProductList;
 

class ProductController extends Controller
{
    use ProductSearch;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(SetProductSortOption::class)->only('index');
    }


    /**
     * Display a listing of the resource.
     *
     * @param Product $model
     * @param ProductFilter $productFilter
     *
     * @return JsonResponse|Application|Factory|View
     */
    public function index(Product $model, ProductFilter $productFilter)
    {
        if (request()->expectsJson()) {
            $categorySlug = request('category');

            if ($categorySlug) {
                $category = Category::findBySlug($categorySlug);

                // Normal kategori varsa mevcut akış
                if ($category->exists) {
                    return $this->searchProducts($model, $productFilter);
                }

                // Normal kategori yoksa, dinamik kategori dene
                $dynamicCategory = DynamicCategory::where('slug', $categorySlug)->first();

                if ($dynamicCategory) {
                    $service = new DynamicCategoryProductService();
                    $query = $service->buildQuery($dynamicCategory);

                    $perPage = (int) request('perPage', 30);
                    $page = max(1, (int) request('page', 1));

                    $all = $query->get();
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
                    $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
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

                    return response()->json([
                        'products' => $paginator,
                        'attributes' => collect(),
                        'category' => [
                            'description_html' => '',
                            'faq_items' => [],
                        ],
                    ]);
                }
            }

            return $this->searchProducts($model, $productFilter);
        }

        return view('storefront::public.products.index');
    }


    /**
     * Show the specified resource.
     *
     * @param string $slug
     *
     * @return Response
     */
    public function show($slug)
    {
        $path = request()->getPathInfo();

        $segments = explode('/', ltrim($path, '/'));
        $supportedLocales = array_keys(LaravelLocalization::getSupportedLocales());
        if (!empty($segments) && in_array($segments[0], $supportedLocales)) {
            array_shift($segments);
        }
        $canonical = '/' . implode('/', $segments);

        $preRedirect = UrlRedirect::query()
            ->where('is_active', true)
            ->where('source_path', $canonical)
            ->first();

        if ($preRedirect) {
            $code = in_array((int) $preRedirect->status_code, [301, 302]) ? (int) $preRedirect->status_code : 301;
            $target = $preRedirect->target_url ?: '/';
            return redirect($target, $code);
        }

        try {
            $product = ProductRepository::findBySlug($slug);
        } catch (\Throwable $e) {
            $sourcePath = '/products/' . ltrim($slug, '/');
            $redirect = UrlRedirect::where('source_path', $sourcePath)
                ->where('is_active', true)
                ->first();

            if ($redirect) {
                $code = in_array((int) $redirect->status_code, [301, 302]) ? (int) $redirect->status_code : 301;
                if ($redirect->target_type === 'product' && $redirect->target_id) {
                    $target = app(ProductRepository::class)->find($redirect->target_id);
                    return redirect($target->url(), $code);
                }
                if ($redirect->target_type === 'category' && $redirect->target_id) {
                    return redirect('/categories/' . $redirect->target_id, $code);
                }
                if ($redirect->target_type === 'home') {
                    return redirect('/', $code);
                }
                if ($redirect->target_type === 'custom' && $redirect->target_url) {
                    return redirect($redirect->target_url, $code);
                }
            }

            $defaultStatus = (int) config('storefront.deleted_product_status', 410);
            abort(in_array($defaultStatus, [404, 410]) ? $defaultStatus : 410);
        }
        $relatedProducts = $product->relatedProducts()->with('variants')->forCard()->get();
        $upSellProducts = $product->upSellProducts()->with('variants')->forCard()->get();
        $review = $this->getReviewData($product);
        $product->append([
            'is_in_flash_sale',
            'unit_min',
            'unit_step',
            'unit_suffix',
            'unit_decimal',
        ]);

        // Ensure productMedia is loaded for frontend JSON (used by Alpine)
        try {
            $product->load(['productMedia' => function ($q) {
                $q->active()->orderBy('position');
            }]);
        } catch (\Throwable $e) {}

        $flashSalePrice = false;

        if ($product->is_in_flash_sale) {
            $pivot = FlashSale::pivot($product);
            if ($pivot->end_date->isFuture()) {
                $flashSalePrice = FlashSale::pivot($product)->price->convertToCurrentCurrency();
            }
        }

        $requestedVariant = request()->query('variant');

        if ($requestedVariant) {
            $product->variant = $product->variants()
                ->withoutGlobalScope('active')
                ->where('uid', $requestedVariant)
                ->firstOrFail();
            $valueUids = array_filter(explode('.', (string) $product->variant->uids));
            $product->loadMissing(['variations.values']);
            $readableParams = [];
            foreach ($product->variations as $variation) {
                $key = Str::slug($variation->name);
                if (!$key) {
                    continue;
                }
                $selectedValue = $variation->values->first(function ($value) use ($valueUids) {
                    return in_array($value->uid, $valueUids, true);
                });
                if (!$selectedValue) {
                    continue;
                }
                $valueSlug = Str::slug($selectedValue->label);
                if (!$valueSlug) {
                    continue;
                }
                $readableParams[$key] = $valueSlug;
            }
            $targetUrl = route('products.show', $product->slug);
            if (!empty($readableParams)) {
                $targetUrl .= '?' . http_build_query($readableParams);
            }
            return redirect()->to($targetUrl, 301);
        } else {
            $product->load(['variations.values']);

            $selectedUids = [];
            foreach ($product->variations as $variation) {
                $key = Str::slug($variation->name);
                $raw = request()->query($key);
                if (!$raw) {
                    continue;
                }

                $valueSlug = Str::slug($raw);
                $valueUid = null;
                foreach ($variation->values as $value) {
                    if (Str::slug($value->label) === $valueSlug) {
                        $valueUid = $value->uid;
                        break;
                    }
                }

                if ($valueUid) {
                    $selectedUids[] = $valueUid;
                }
            }

            if (!empty($selectedUids)) {
                sort($selectedUids);
                $uidsString = implode('.', $selectedUids);

                $matched = $product->variants()
                    ->withoutGlobalScope('active')
                    ->where('uids', $uidsString)
                    ->first();

                if ($matched) {
                    $product->variant = $matched;
                } else {
                    $product->variant = $product->variants()
                        ->withoutGlobalScope('active')
                        ->default()
                        ->first();
                }
            } else {
                $product->variant = $product->variants()
                    ->withoutGlobalScope('active')
                    ->default()
                    ->first();
            }
        }

        // Build unified gallery: base image first, then other images, then videos (by extension)
        $gallery = [];
        $baseId = $product->base_image->id ?? null;

        if ($product->base_image) {
            $gallery[] = [
                'type' => 'image',
                'src' => $product->base_image->detail_jpeg_url ?? $product->base_image->path,
                'thumb' => $product->base_image->thumb_jpeg_url ?? $product->base_image->path,
                'alt' => $product->name,
            ];
        }

        foreach ($product->media as $media) {
            $rawPath = $media->getRawOriginal('path');
            $ext = strtolower(pathinfo((string) $rawPath, PATHINFO_EXTENSION));
            $isVideo = in_array($ext, ['mp4', 'webm', 'ogg']);
            if (!$isVideo) {
                // skip base image duplicate
                if ($baseId && $media->id === $baseId) continue;
                $gallery[] = [
                    'type' => 'image',
                    'src' => $media->detail_jpeg_url ?? $media->path,
                    'thumb' => $media->thumb_jpeg_url ?? $media->path,
                    'alt' => $product->name,
                ];
            }
        }

        $variantVideos = collect();
        try {
            if ($product->variant) {
                $variantVideos = $product->productMedia()->active()->videos()->where('variant_id', $product->variant->id)->get();
                if ($variantVideos->isEmpty()) {
                    $variantVideos = $product->productMedia()->active()->videos()->whereNull('variant_id')->get();
                }
            } else {
                $variantVideos = $product->productMedia()->active()->videos()->whereNull('variant_id')->get();
            }
        } catch (\Throwable $e) {}

        foreach ($variantVideos as $media) {
            $variantBase = optional($product->variant)->base_image?->path;
            $poster = $media->poster ?: ($variantBase ?: ($product->base_image?->path ?? asset('build/assets/image-placeholder.png')));
            $gallery[] = [
                'type' => 'video',
                'src' => $media->path,
                'thumb' => $poster,
                'alt' => 'Ürün video – ' . $product->name,
            ];
        }

        if (!empty($gallery)) {
            if ($gallery[0]['type'] !== 'image') {
                // ensure first item is always image for LCP
                usort($gallery, function ($a, $b) {
                    return ($a['type'] === 'image' ? 0 : 1) <=> ($b['type'] === 'image' ? 0 : 1);
                });
            }
        }

        event(new ProductViewed($product));

        $firstVideo = null;
        foreach ($gallery as $gi) {
            if (($gi['type'] ?? '') === 'video') { $firstVideo = $gi; break; }
        }
        $hasVideo = $firstVideo !== null;
        $videoUrl = $hasVideo ? ($firstVideo['src'] ?? null) : null;
        $videoThumbnailUrl = $hasVideo ? ($firstVideo['thumb'] ?? ($product->base_image?->path ?? null)) : null;

        return view('storefront::public.products.show', compact('product', 'relatedProducts', 'upSellProducts', 'review', 'flashSalePrice', 'gallery', 'hasVideo', 'videoUrl', 'videoThumbnailUrl'));
    }


    private function getReviewData(Product $product)
    {
        if (!setting('reviews_enabled')) {
            return null;
        }

        return Review::countAndAvgRating($product);
    }
}
