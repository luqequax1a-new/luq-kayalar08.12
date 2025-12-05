<?php

namespace Modules\Support\Http\Controllers\Admin;

use Modules\Support\Services\SitemapService;

class SitemapController
{
    public function create(SitemapService $sitemapService)
    {
        return view('support::admin.sitemap.index');
    }


    public function store(SitemapService $sitemapService)
    {
        $data = request()->all();

        $payload = [
            // include flags
            'support.sitemap.include_products' => (bool) ($data['include_products'] ?? true),
            'support.sitemap.include_categories' => (bool) ($data['include_categories'] ?? true),
            'support.sitemap.include_pages' => (bool) ($data['include_pages'] ?? true),
            'support.sitemap.include_brands' => (bool) ($data['include_brands'] ?? true),
            'support.sitemap.include_blog_posts' => (bool) ($data['include_blog_posts'] ?? true),
            'support.sitemap.include_blog_categories' => (bool) ($data['include_blog_categories'] ?? true),
            'support.sitemap.include_other_pages' => (bool) ($data['include_other_pages'] ?? false),

            // priorities
            'support.sitemap.products_priority' => (float) ($data['products_priority'] ?? 0.7),
            'support.sitemap.categories_priority' => (float) ($data['categories_priority'] ?? 0.8),
            'support.sitemap.pages_priority' => (float) ($data['pages_priority'] ?? 0.5),
            'support.sitemap.brands_priority' => (float) ($data['brands_priority'] ?? 0.5),
            'support.sitemap.blog_posts_priority' => (float) ($data['blog_posts_priority'] ?? 0.6),
            'support.sitemap.blog_categories_priority' => (float) ($data['blog_categories_priority'] ?? 0.5),

            // changefreqs
            'support.sitemap.products_changefreq' => (string) ($data['products_changefreq'] ?? \Spatie\Sitemap\Tags\Url::CHANGE_FREQUENCY_WEEKLY),
            'support.sitemap.categories_changefreq' => (string) ($data['categories_changefreq'] ?? \Spatie\Sitemap\Tags\Url::CHANGE_FREQUENCY_DAILY),
            'support.sitemap.pages_changefreq' => (string) ($data['pages_changefreq'] ?? \Spatie\Sitemap\Tags\Url::CHANGE_FREQUENCY_WEEKLY),
            'support.sitemap.brands_changefreq' => (string) ($data['brands_changefreq'] ?? \Spatie\Sitemap\Tags\Url::CHANGE_FREQUENCY_WEEKLY),
            'support.sitemap.blog_posts_changefreq' => (string) ($data['blog_posts_changefreq'] ?? \Spatie\Sitemap\Tags\Url::CHANGE_FREQUENCY_WEEKLY),
            'support.sitemap.blog_categories_changefreq' => (string) ($data['blog_categories_changefreq'] ?? \Spatie\Sitemap\Tags\Url::CHANGE_FREQUENCY_WEEKLY),

            // products per sitemap (chunk size)
            'support.sitemap.products_per_sitemap' => (int) ($data['products_per_sitemap'] ?? 10000),

            // optional cron token
            'support.sitemap.cron_token' => (string) ($data['cron_token'] ?? ''),
        ];

        setting($payload);

        $sitemapService->generate();

        return back()->with('success', trans('support::sitemap.messages.sitemap_generated_successfully'));
    }
}

