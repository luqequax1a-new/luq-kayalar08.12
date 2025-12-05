<?php

namespace Modules\Ticket\Providers;

use Illuminate\Support\ServiceProvider;

class TicketServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'ticket');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'ticket');
    }

    public function register(): void
    {
    }
}
