<?php

namespace Modules\ProductFeeds\Http\Controllers\Public;

use Illuminate\Http\Response;
use Modules\ProductFeeds\Services\FeedCacheService;

class CronRefreshController
{
    public function __construct(private readonly FeedCacheService $cache)
    {
    }

    public function handle(string $channel): Response
    {
        $validChannels = ['google', 'meta', 'tiktok', 'trendyol', 'pinterest'];

        if (! in_array($channel, $validChannels, true)) {
            abort(404);
        }

        $token = (string) request()->query('token', '');
        $expected = (string) setting('product_feeds.cache.token', '');

        if ($expected === '' || ! hash_equals($expected, $token)) {
            abort(403);
        }

        // Force regeneration regardless of current cache state
        switch ($channel) {
            case 'google':
                $controller = app(GoogleFeedController::class);
                $response = $controller->generate();
                $contentType = 'application/xml; charset=UTF-8';
                break;
            case 'meta':
                $controller = app(MetaFeedController::class);
                $response = $controller->generate();
                $contentType = 'application/json; charset=UTF-8';
                break;
            case 'tiktok':
                $controller = app(TikTokFeedController::class);
                $response = $controller->generate();
                $contentType = 'application/json; charset=UTF-8';
                break;
            case 'trendyol':
                $controller = app(TrendyolFeedController::class);
                $response = $controller->generate();
                $contentType = 'application/xml; charset=UTF-8';
                break;
            case 'pinterest':
                $controller = app(PinterestFeedController::class);
                $response = $controller->generate();
                $format = setting('product_feeds.pinterest.format', 'tsv');
                $contentType = $format === 'csv'
                    ? 'text/csv; charset=UTF-8'
                    : 'text/tab-separated-values; charset=UTF-8';
                break;
        }

        $this->cache->writeCache($channel, (string) $response->getContent());

        return new Response('OK', 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
