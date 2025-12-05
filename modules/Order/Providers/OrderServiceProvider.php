<?php

namespace Modules\Order\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Artisan;
use Modules\Order\Console\Commands\BackfillOrderAddresses;
use Modules\Order\Console\Commands\SendSecondReviewRequestEmails;
use Modules\Order\Console\Commands\CreateGeliverTestOrder;

class OrderServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                BackfillOrderAddresses::class,
                SendSecondReviewRequestEmails::class,
                CreateGeliverTestOrder::class,
            ]);
        }
    }
}
