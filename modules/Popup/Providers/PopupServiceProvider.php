<?php

namespace Modules\Popup\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Modules\Popup\Services\PopupMatcher;

class PopupServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PopupMatcher::class, function () {
            return new PopupMatcher();
        });
    }

    public function boot(): void
    {
        View::composer('storefront::public.layout', function ($view) {
            $matcher = $this->app->make(PopupMatcher::class);
            $view->with('activePopup', $matcher->matchForRequest(request()));
        });
    }
}
