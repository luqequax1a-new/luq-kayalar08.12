<?php

namespace Modules\ProductFeeds\Http\Controllers\Public;

use Illuminate\Http\JsonResponse;
use Modules\ProductFeeds\Services\FeedCacheService;
use Modules\ProductFeeds\Services\ProductFeedBuilder;

class TikTokFeedController
{
    public function __construct(
        private readonly ProductFeedBuilder $feeds,
        private readonly FeedCacheService $cache,
    )
    {
    }

    public function index(): JsonResponse
    {
        if (! setting('product_feeds.global.enabled', true) || ! setting('product_feeds.tiktok.enabled', true)) {
            abort(404);
        }

        $channel = 'tiktok';

        if ($this->cache->isEnabled() && ! $this->cache->shouldRegenerate($channel)) {
            $cached = $this->cache->readCache($channel);

            if ($cached !== null) {
                return new JsonResponse(json_decode($cached, true), 200);
            }
        }

        $response = $this->generate();

        if ($this->cache->isEnabled()) {
            $this->cache->writeCache($channel, (string) $response->getContent());
        }

        return $response;
    }

    public function generate(): JsonResponse
    {
        $rows = $this->feeds->normalizedItemsForFeed('tiktok');

        if (setting('product_feeds.tiktok.in_stock_only', true)) {
            $rows = $rows->filter(function (array $row) {
                return $row['availability'] === 'in stock';
            })->values();
        }

        $shippingProfile = setting('product_feeds.tiktok.shipping_profile');

        $items = [];

        foreach ($rows as $row) {
            $images = [];

            if (! empty($row['main_image'])) {
                $images[] = $row['main_image'];
            }

            if (! empty($row['additional_images'])) {
                $images = array_merge($images, $row['additional_images']);
            }

            $items[] = [
                'id' => (string) $row['id'],
                'title' => $row['title'],
                'description' => $row['description'],
                'price' => (float) ($row['sale_price'] ?? $row['price']),
                'currency' => $row['currency'],
                'stock' => $row['stock'] ?? 0,
                'brand' => $row['brand'],
                'sku' => $row['sku'],
                'url' => $row['url'],
                'images' => $images,
                'category' => $row['category_path'],
                'weight' => $row['weight'],
                'shipping' => [
                    'price' => null,
                    'currency' => $row['currency'],
                    'profile' => $shippingProfile ?: null,
                ],
            ];
        }

        return response()->json(['items' => $items]);
    }
}
