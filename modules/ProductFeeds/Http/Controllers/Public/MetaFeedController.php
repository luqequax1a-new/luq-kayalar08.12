<?php

namespace Modules\ProductFeeds\Http\Controllers\Public;

use Illuminate\Http\JsonResponse;
use Modules\ProductFeeds\Services\FeedCacheService;
use Modules\ProductFeeds\Services\ProductFeedBuilder;

class MetaFeedController
{
    public function __construct(
        private readonly ProductFeedBuilder $feeds,
        private readonly FeedCacheService $cache,
    )
    {
    }

    public function index(): JsonResponse
    {
        if (! setting('product_feeds.global.enabled', true) || ! setting('product_feeds.meta.enabled', true)) {
            abort(404);
        }

        $channel = 'meta';

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
        $rows = $this->feeds->normalizedItemsForFeed('meta');

        $data = [];

        foreach ($rows as $row) {
            $item = [
                'id' => (string) $row['id'],
                'availability' => $row['availability'],
                'condition' => 'new',
                'description' => $row['description'],
                'image_link' => $row['main_image'],
                'link' => $row['url'],
                'title' => $row['title'],
                'brand' => $row['brand'],
                'price' => sprintf('%.2f %s', (float) $row['price'], $row['currency']),
                'google_product_category' => $row['google_category'],
                'product_type' => $row['category_path'],
                'item_group_id' => $row['item_group_id'],
            ];

            if (! empty($row['additional_images'])) {
                $item['additional_image_link'] = $row['additional_images'];
            }

            if (! is_null($row['sale_price'])) {
                $item['sale_price'] = sprintf('%.2f %s', (float) $row['sale_price'], $row['currency']);
            }

            $data[] = $item;
        }

        return response()->json(['data' => $data]);
    }
}
