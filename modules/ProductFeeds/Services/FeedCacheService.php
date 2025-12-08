<?php

namespace Modules\ProductFeeds\Services;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class FeedCacheService
{
    public function __construct(private readonly Filesystem $files)
    {
    }

    public function isEnabled(): bool
    {
        return (bool) setting('product_feeds.cache.enabled', false);
    }

    public function getCachePath(string $channel): string
    {
        $directory = storage_path('app/feeds');

        return $directory . DIRECTORY_SEPARATOR . match ($channel) {
            'google' => 'google.xml',
            'meta' => 'meta.json',
            'tiktok' => 'tiktok.json',
            'trendyol' => 'trendyol.xml',
            'pinterest' => 'pinterest.tsv',
            default => Str::slug($channel) . '.cache',
        };
    }

    public function shouldRegenerate(string $channel): bool
    {
        if (! $this->isEnabled()) {
            return true;
        }

        $path = $this->getCachePath($channel);

        if (! $this->files->exists($path)) {
            return true;
        }

        $minutes = (int) match ($channel) {
            'google' => setting('product_feeds.cache.google', 60),
            'meta' => setting('product_feeds.cache.meta', 60),
            'tiktok' => setting('product_feeds.cache.tiktok', 60),
            'trendyol' => setting('product_feeds.cache.trendyol', 60),
            'pinterest' => setting('product_feeds.cache.pinterest', 60),
            default => 60,
        };

        if ($minutes <= 0) {
            return true;
        }

        $modified = $this->files->lastModified($path);

        return $modified + ($minutes * 60) < time();
    }

    public function writeCache(string $channel, string $content): void
    {
        $directory = storage_path('app/feeds');

        if (! $this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }

        $this->files->put($this->getCachePath($channel), $content, true);
    }

    public function readCache(string $channel): ?string
    {
        $path = $this->getCachePath($channel);

        if (! $this->files->exists($path)) {
            return null;
        }

        return $this->files->get($path);
    }

    public function refresh(string $channel, callable $generator): string
    {
        $content = (string) $generator();

        $this->writeCache($channel, $content);

        return $content;
    }
}
