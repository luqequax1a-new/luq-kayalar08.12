<?php

namespace Modules\Shipping\Providers;

use Modules\Shipping\Method;
use Illuminate\Support\ServiceProvider;
use Modules\Shipping\Facades\ShippingMethod;

class ShippingServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(): void
    {
        if (!config('app.installed')) {
            return;
        }

        if (setting('smart_shipping_enabled')) {
            $this->registerSmartShipping();
            $this->registerLocalPickup();

            return;
        }

        $this->registerFreeShipping();
        $this->registerLocalPickup();
        $this->registerFlatRate();
    }


    private function registerSmartShipping(): void
    {
        ShippingMethod::register('smart_shipping', function () {
            $label = setting('smart_shipping_name') ?: 'Standard Shipping';
            $baseRate = (float) (setting('smart_shipping_base_rate') ?? 0);

            // ShippingServiceProvider must remain stateless with respect to Cart.
            // We only register SmartShipping metadata here; the dynamic cost is
            // computed later in the checkout layer via SmartShippingCalculator.

            return new Method('smart_shipping', $label, $baseRate);
        });
    }


    private function registerFreeShipping()
    {
        if (!setting('free_shipping_enabled')) {
            return;
        }

        ShippingMethod::register('free_shipping', function () {
            return new Method('free_shipping', setting('free_shipping_label'), 0);
        });
    }


    private function registerLocalPickup()
    {
        if (!setting('local_pickup_enabled')) {
            return;
        }

        ShippingMethod::register('local_pickup', function () {
            return new Method('local_pickup', setting('local_pickup_label'), setting('local_pickup_cost') ?? 0);
        });
    }


    private function registerFlatRate()
    {
        if (!setting('flat_rate_enabled')) {
            return;
        }

        ShippingMethod::register('flat_rate', function () {
            return new Method('flat_rate', setting('flat_rate_label'), setting('flat_rate_cost') ?? 0);
        });
    }
}
