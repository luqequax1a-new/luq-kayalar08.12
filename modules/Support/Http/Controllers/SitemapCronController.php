<?php

namespace Modules\Support\Http\Controllers;

use Illuminate\Http\Response;
use Modules\Support\Services\SitemapService;

class SitemapCronController
{
    public function run(string $token, SitemapService $sitemapService)
    {
        $configuredToken = (string) setting('support.sitemap.cron_token', '');

        if ($configuredToken === '' || !hash_equals($configuredToken, (string) $token)) {
            abort(403);
        }

        $sitemapService->generate();

        return new Response('Sitemap generated', 200);
    }
}
