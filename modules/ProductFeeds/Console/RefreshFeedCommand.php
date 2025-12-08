<?php

namespace Modules\ProductFeeds\Console;

use Illuminate\Console\Command;
use Modules\ProductFeeds\Http\Controllers\Public\GoogleFeedController;
use Modules\ProductFeeds\Http\Controllers\Public\MetaFeedController;
use Modules\ProductFeeds\Http\Controllers\Public\TikTokFeedController;
use Modules\ProductFeeds\Http\Controllers\Public\TrendyolFeedController;
use Modules\ProductFeeds\Http\Controllers\Public\PinterestFeedController;
use Modules\ProductFeeds\Services\FeedCacheService;

class RefreshFeedCommand extends Command
{
    protected $signature = 'feeds:refresh {channel : google|meta|tiktok|trendyol|pinterest}';

    protected $description = 'Regenerate and cache a product feed for the given channel.';

    public function handle(): int
    {
        $channel = (string) $this->argument('channel');

        $validChannels = ['google', 'meta', 'tiktok', 'trendyol', 'pinterest'];

        if (! in_array($channel, $validChannels, true)) {
            $this->error('Invalid channel. Allowed: ' . implode(', ', $validChannels));

            return 1;
        }

        /** @var FeedCacheService $cache */
        $cache = app(FeedCacheService::class);

        switch ($channel) {
            case 'google':
                $controller = app(GoogleFeedController::class);
                $response = $controller->generate();
                break;
            case 'meta':
                $controller = app(MetaFeedController::class);
                $response = $controller->generate();
                break;
            case 'tiktok':
                $controller = app(TikTokFeedController::class);
                $response = $controller->generate();
                break;
            case 'trendyol':
                $controller = app(TrendyolFeedController::class);
                $response = $controller->generate();
                break;
            case 'pinterest':
                $controller = app(PinterestFeedController::class);
                $response = $controller->generate();
                break;
        }

        $cache->writeCache($channel, (string) $response->getContent());

        $this->info('Feed cache refreshed for channel: ' . $channel);

        return 0;
    }
}
