<?php

namespace Modules\ProductFeeds\Http\Controllers\Public;

use Illuminate\Http\Response;
use Modules\ProductFeeds\Services\FeedCacheService;
use Modules\ProductFeeds\Services\ProductFeedBuilder;

class GoogleFeedController
{
    public function __construct(
        private readonly ProductFeedBuilder $feeds,
        private readonly FeedCacheService $cache,
    )
    {
    }

    public function index(): Response
    {
        if (! setting('product_feeds.global.enabled', true) || ! setting('product_feeds.google.enabled', true)) {
            abort(404);
        }
        $channel = 'google';

        if ($this->cache->isEnabled() && ! $this->cache->shouldRegenerate($channel)) {
            $cached = $this->cache->readCache($channel);

            if ($cached !== null) {
                return new Response($cached, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
            }
        }

        $response = $this->generate();

        if ($this->cache->isEnabled()) {
            $this->cache->writeCache($channel, (string) $response->getContent());
        }

        return $response;
    }

    public function generate(): Response
    {
        $items = $this->feeds->normalizedItemsForFeed('google');

        $storeName = (string) setting('store_name');
        $storeUrl = url('/');
        $storeTagline = (string) (setting('store_tagline') ?: $storeName);

        $xml = [];
        $xml[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml[] = '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">';
        $xml[] = '<channel>';
        $xml[] = '<title>' . e($storeName) . '</title>';
        $xml[] = '<link>' . e($storeUrl) . '</link>';
        $xml[] = '<description>' . e($storeTagline) . '</description>';

        $behavior = (string) setting('product_feeds.google.missing_identifier_behavior', 'mpn_from_id');

        foreach ($items as $row) {
            $xml[] = '<item>';
            $xml[] = '<g:id>' . e($row['id']) . '</g:id>';
            $xml[] = '<title>' . e($row['title']) . '</title>';
            $xml[] = '<description>' . e($row['description']) . '</description>';
            $xml[] = '<link>' . e($row['url']) . '</link>';

            if (! empty($row['main_image'])) {
                $xml[] = '<g:image_link>' . e($row['main_image']) . '</g:image_link>';
            }

            foreach ($row['additional_images'] as $image) {
                $xml[] = '<g:additional_image_link>' . e($image) . '</g:additional_image_link>';
            }

            $xml[] = '<g:availability>' . e($row['availability']) . '</g:availability>';

            $price = number_format((float) $row['price'], 2, '.', '') . ' ' . $row['currency'];
            $xml[] = '<g:price>' . e($price) . '</g:price>';

            if (! is_null($row['sale_price'])) {
                $sale = number_format((float) $row['sale_price'], 2, '.', '') . ' ' . $row['currency'];
                $xml[] = '<g:sale_price>' . e($sale) . '</g:sale_price>';
            }

            $xml[] = '<g:condition>new</g:condition>';

            if (! empty($row['brand'])) {
                $xml[] = '<g:brand>' . e($row['brand']) . '</g:brand>';
            }

            if ($behavior === 'mpn_from_id') {
                $mpn = $row['sku'] ?: $row['id'];
                $xml[] = '<g:mpn>' . e($mpn) . '</g:mpn>';
            }

            if (! empty($row['google_category'])) {
                $xml[] = '<g:google_product_category>' . e($row['google_category']) . '</g:google_product_category>';
            }

            $productType = $row['product_type'] ?? ($row['category_path'] ?? '');

            if ($productType !== '') {
                $xml[] = '<g:product_type>' . e($productType) . '</g:product_type>';
            }

            $xml[] = '</item>';
        }

        $xml[] = '</channel>';
        $xml[] = '</rss>';

        $content = implode("\n", $xml);

        return new Response($content, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
