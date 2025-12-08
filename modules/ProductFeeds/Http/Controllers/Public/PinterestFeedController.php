<?php

namespace Modules\ProductFeeds\Http\Controllers\Public;

use Illuminate\Http\Response;
use Modules\ProductFeeds\Services\FeedCacheService;
use Modules\ProductFeeds\Services\ProductFeedBuilder;

class PinterestFeedController
{
    public function __construct(
        private readonly ProductFeedBuilder $feeds,
        private readonly FeedCacheService $cache,
    )
    {
    }

    public function index(): Response
    {
        if (! setting('product_feeds.global.enabled', true) || ! setting('product_feeds.pinterest.enabled', true)) {
            abort(404);
        }

        $channel = 'pinterest';

        $format = setting('product_feeds.pinterest.format', 'tsv');
        $delimiter = $format === 'csv' ? ',' : "\t";
        $contentType = $format === 'csv'
            ? 'text/csv; charset=UTF-8'
            : 'text/tab-separated-values; charset=UTF-8';

        if ($this->cache->isEnabled() && ! $this->cache->shouldRegenerate($channel)) {
            $cached = $this->cache->readCache($channel);

            if ($cached !== null) {
                return new Response($cached, 200, ['Content-Type' => $contentType]);
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
        $rows = $this->feeds->normalizedItemsForFeed('pinterest');

        $format = setting('product_feeds.pinterest.format', 'tsv');
        $delimiter = $format === 'csv' ? ',' : "\t";
        $contentType = $format === 'csv'
            ? 'text/csv; charset=UTF-8'
            : 'text/tab-separated-values; charset=UTF-8';

        $columns = [
            'id',
            'title',
            'description',
            'link',
            'image_link',
            'availability',
            'price',
            'sale_price',
            'brand',
            'condition',
            'google_product_category',
            'product_type',
            'item_group_id',
        ];

        $lines = [];
        $lines[] = implode($delimiter, $columns);

        foreach ($rows as $row) {
            $price = sprintf('%.2f %s', (float) $row['price'], $row['currency']);
            $salePrice = '';

            if (! is_null($row['sale_price'])) {
                $salePrice = sprintf('%.2f %s', (float) $row['sale_price'], $row['currency']);
            }

            $fields = [
                $row['id'],
                $row['title'],
                $row['description'],
                $row['url'],
                $row['main_image'],
                $row['availability'],
                $price,
                $salePrice,
                $row['brand'],
                'new',
                $row['google_category'] ?? '',
                $row['category_path'] ?? '',
                $row['item_group_id'],
            ];

            $sanitized = array_map(function ($value) use ($delimiter, $format) {
                $value = (string) ($value ?? '');
                $value = str_replace(["\r", "\n", "\t"], ' ', $value);

                if ($format === 'csv' && str_contains($value, $delimiter)) {
                    $value = '"' . str_replace('"', '""', $value) . '"';
                }

                return $value;
            }, $fields);

            $lines[] = implode($delimiter, $sanitized);
        }

        $content = implode("\n", $lines);

        return new Response($content, 200, ['Content-Type' => $contentType]);
    }
}
