<?php

namespace Modules\Media\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Media\Console\GenerateResponsiveVariants;

class MediaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $path = __DIR__.'/../Support/helpers.php';
        if (file_exists($path)) require_once $path;

        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateResponsiveVariants::class,
            ]);
        }
    }
}
