<?php

namespace Modules\ProductFeeds\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class ProductFeedSettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'global' => [
                'enabled' => (bool) setting('product_feeds.global.enabled', true),
                'brand_name' => (string) setting('product_feeds.global.brand_name', setting('store_name')),
                'country' => (string) setting('product_feeds.global.country', 'TR'),
                'currency' => (string) setting('product_feeds.global.currency', currency()),
                'include_out_of_stock' => (bool) setting('product_feeds.global.include_out_of_stock', false),
                'include_unpublished' => (bool) setting('product_feeds.global.include_unpublished', false),
                'include_variants' => (bool) setting('product_feeds.global.include_variants', true),
                'locale' => (string) setting('product_feeds.global.locale', locale()),
            ],
            'google' => [
                'enabled' => (bool) setting('product_feeds.google.enabled', true),
                'category' => (string) setting('product_feeds.google.category', ''),
                'missing_identifier_behavior' => (string) setting('product_feeds.google.missing_identifier_behavior', 'mpn_from_id'),
                'use_store_tax' => (bool) setting('product_feeds.google.use_store_tax', true),
                'shipping_price' => (string) setting('product_feeds.google.shipping_price', ''),
            ],
            'meta' => [
                'enabled' => (bool) setting('product_feeds.meta.enabled', true),
                'categories' => (string) setting('product_feeds.meta.categories', ''),
                'use_variants' => (bool) setting('product_feeds.meta.use_variants', true),
            ],
            'tiktok' => [
                'enabled' => (bool) setting('product_feeds.tiktok.enabled', true),
                'shipping_profile' => (string) setting('product_feeds.tiktok.shipping_profile', ''),
                'in_stock_only' => (bool) setting('product_feeds.tiktok.in_stock_only', true),
            ],
            'trendyol' => [
                'enabled' => (bool) setting('product_feeds.trendyol.enabled', true),
                'supplier_id' => (string) setting('product_feeds.trendyol.supplier_id', ''),
                'brand' => (string) setting('product_feeds.trendyol.brand', ''),
                'cargo_company' => (string) setting('product_feeds.trendyol.cargo_company', ''),
                'vat_rate' => (string) setting('product_feeds.trendyol.vat_rate', ''),
            ],
            'pinterest' => [
                'enabled' => (bool) setting('product_feeds.pinterest.enabled', true),
                'category' => (string) setting('product_feeds.pinterest.category', ''),
                'format' => (string) setting('product_feeds.pinterest.format', 'tsv'),
            ],
            'cache' => [
                'enabled' => (bool) setting('product_feeds.cache.enabled', false),
                'google' => (int) setting('product_feeds.cache.google', 60),
                'meta' => (int) setting('product_feeds.cache.meta', 60),
                'tiktok' => (int) setting('product_feeds.cache.tiktok', 60),
                'trendyol' => (int) setting('product_feeds.cache.trendyol', 60),
                'pinterest' => (int) setting('product_feeds.cache.pinterest', 60),
                'token' => (string) setting('product_feeds.cache.token', ''),
            ],
        ];

        if ($settings['cache']['token'] === '') {
            $token = Str::random(32);
            setting(['product_feeds.cache.token' => $token]);
            $settings['cache']['token'] = $token;
        }

        return view('product_feeds::admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'global.enabled' => 'sometimes|boolean',
            'global.brand_name' => 'nullable|string|max:255',
            'global.country' => 'nullable|string|max:2',
            'global.currency' => 'nullable|string|max:3',
            'global.include_out_of_stock' => 'sometimes|boolean',
            'global.include_unpublished' => 'sometimes|boolean',
            'global.include_variants' => 'sometimes|boolean',
            'global.locale' => 'nullable|string|max:10',

            'google.enabled' => 'sometimes|boolean',
            'google.category' => 'nullable|string|max:255',
            'google.missing_identifier_behavior' => 'nullable|string|in:empty,mpn_from_id',
            'google.use_store_tax' => 'sometimes|boolean',
            'google.shipping_price' => 'nullable|string|max:50',

            'meta.enabled' => 'sometimes|boolean',
            'meta.categories' => 'nullable|string|max:255',
            'meta.use_variants' => 'sometimes|boolean',

            'tiktok.enabled' => 'sometimes|boolean',
            'tiktok.shipping_profile' => 'nullable|string|max:255',
            'tiktok.in_stock_only' => 'sometimes|boolean',

            'trendyol.enabled' => 'sometimes|boolean',
            'trendyol.supplier_id' => 'nullable|string|max:255',
            'trendyol.brand' => 'nullable|string|max:255',
            'trendyol.cargo_company' => 'nullable|string|max:255',
            'trendyol.vat_rate' => 'nullable|string|max:10',

            'pinterest.enabled' => 'sometimes|boolean',
            'pinterest.category' => 'nullable|string|max:255',
            'pinterest.format' => 'nullable|string|in:tsv,csv',

            'cache.enabled' => 'sometimes|boolean',
            'cache.google' => 'nullable|integer|min:0',
            'cache.meta' => 'nullable|integer|min:0',
            'cache.tiktok' => 'nullable|integer|min:0',
            'cache.trendyol' => 'nullable|integer|min:0',
            'cache.pinterest' => 'nullable|integer|min:0',
        ]);

        $settings = [];

        foreach ($data as $section => $values) {
            if (is_array($values)) {
                foreach ($values as $key => $value) {
                    $settings["product_feeds.{$section}.{$key}"] = is_bool($value) ? (int) $value : $value;
                }
            }
        }

        setting($settings);

        return redirect()->back()->with('success', trans('admin::messages.saved')); 
    }
}
