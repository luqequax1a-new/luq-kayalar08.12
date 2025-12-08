<?php

namespace Modules\ProductFeeds\Providers;

use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! config('app.installed')) {
            return;
        }
    }
}
