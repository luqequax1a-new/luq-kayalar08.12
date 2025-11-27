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
                    $target = ProductRepository::find($redirect->target_id);
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
        }

        event(new ProductViewed($product));

        return view('storefront::public.products.show', compact('product', 'relatedProducts', 'upSellProducts', 'review', 'flashSalePrice'));
    }


    private function getReviewData(Product $product)
    {
        if (!setting('reviews_enabled')) {
            return null;
        }

        return Review::countAndAvgRating($product);
    }
}
