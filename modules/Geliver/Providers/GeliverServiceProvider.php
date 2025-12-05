<?php

namespace Modules\Geliver\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Modules\Order\Events\OrderStatusChanged;
use Modules\Geliver\Listeners\SendOrderToGeliverOnProcessing;

class GeliverServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'geliver');
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'geliver');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->mergeConfigFrom(__DIR__.'/../Config/geliver.php', 'geliver');

        Event::listen(OrderStatusChanged::class, [SendOrderToGeliverOnProcessing::class, 'handle']);
    }

    public function register(): void
    {
    }
}
