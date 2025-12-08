<?php

namespace Modules\ProductFeeds\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\ProductFeeds\Http\Controllers\Public\GoogleFeedController;
use Modules\ProductFeeds\Http\Controllers\Public\MetaFeedController;
use Modules\ProductFeeds\Http\Controllers\Public\TikTokFeedController;
use Modules\ProductFeeds\Http\Controllers\Public\TrendyolFeedController;
use Modules\ProductFeeds\Http\Controllers\Public\PinterestFeedController;
use Modules\ProductFeeds\Services\FeedCacheService;

class FeedCacheController extends Controller
{
    public function __construct(private readonly FeedCacheService $cache)
    {
    }

    public function refresh(string $channel): RedirectResponse
    {
        $validChannels = ['google', 'meta', 'tiktok', 'trendyol', 'pinterest'];

        if (! in_array($channel, $validChannels, true)) {
            abort(404);
        }

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

        $this->cache->writeCache($channel, (string) $response->getContent());

        return redirect()->back()->with('success', trans('product_feeds::messages.cache_refreshed'));
    }

    public function regenerateToken(): RedirectResponse
    {
        $token = Str::random(32);

        setting(['product_feeds.cache.token' => $token]);

        return redirect()->back()->with('success', 'Cron token regenerated.');
    }
}
