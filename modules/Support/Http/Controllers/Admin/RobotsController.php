<?php

namespace Modules\Support\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\Support\Services\SitemapService;

class RobotsController
{
    public function edit(SitemapService $sitemapService): View
    {
        $path = public_path('robots.txt');
        $robotsContent = '';

        if (is_readable($path)) {
            $robotsContent = @file_get_contents($path) ?: '';
        }

        if ($robotsContent === '') {
            $robotsContent = $this->defaultRobotsContent();
        }

        $robotsUrl = url('robots.txt');
        $sitemapUrl = url('sitemap.xml');

        return view('support::admin.robots.edit', compact('robotsContent', 'robotsUrl', 'sitemapUrl'));
    }


    public function update(Request $request, SitemapService $sitemapService): RedirectResponse|Redirector
    {
        $data = $request->validate([
            'robots' => ['required', 'string', 'max:100000'],
        ]);

        $path = public_path('robots.txt');

        try {
            $result = @file_put_contents($path, $data['robots']);

            if ($result === false) {
                return redirect()->back()->with('error', trans('support::robots.messages.write_failed'));
            }

            // Ensure sitemap line stays in sync
            $sitemapService->generate();
        } catch (\Throwable $e) {
            Log::error('Robots.txt update failed: ' . $e->getMessage());

            return redirect()->back()->with('error', trans('support::robots.messages.write_failed'));
        }

        return redirect()->back()->with('success', trans('support::robots.messages.updated'));
    }


    public function reset(SitemapService $sitemapService): RedirectResponse|Redirector
    {
        $path = public_path('robots.txt');

        try {
            $default = $this->defaultRobotsContent();
            $result = @file_put_contents($path, $default);

            if ($result === false) {
                return redirect()->back()->with('error', trans('support::robots.messages.write_failed'));
            }

            // Keep sitemap line consistent with current domain
            $sitemapService->generate();
        } catch (\Throwable $e) {
            Log::error('Robots.txt reset failed: ' . $e->getMessage());

            return redirect()->back()->with('error', trans('support::robots.messages.write_failed'));
        }

        return redirect()->route('admin.robots.edit')->with('success', trans('support::robots.messages.reset'));
    }


    protected function defaultRobotsContent(): string
    {
        $sitemapUrl = url('sitemap.xml');

        $lines = [
            'User-agent: *',
            'Disallow: /admin/',
            'Disallow: /cpanel/',
            'Disallow: /vendor/',
            'Disallow: /webserver/',
            'Disallow: /error/',
            'Disallow: /storage/',
            'Disallow: /debugbar/',
            '',
            '# Account & checkout flows',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /password/',
            'Disallow: /cart',
            'Disallow: /checkout',
            'Disallow: /orders',
            'Disallow: /account',
            '',
            '# Search and pagination (can produce duplicate content)',
            'Disallow: /search',
            'Disallow: /*?q=',
            'Disallow: /*?page=',
            'Disallow: /*&page=',
            '',
            '# Internal technical paths (if they exist)',
            'Disallow: /api/',
            'Disallow: /_debugbar/',
            '',
            '# Important: DO NOT block static assets so Google can render the site',
            'Allow: /modules/',
            'Allow: /themes/',
            'Allow: /assets/',
            'Allow: /storage/cache/',
            'Allow: /storage/media/',
            '',
            'Sitemap: ' . $sitemapUrl,
        ];

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }
}
