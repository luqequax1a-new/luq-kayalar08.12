<?php

namespace Modules\Shipping\Services;

use Modules\Cart\Facades\Cart;
use Modules\Support\Money;

class SmartShippingCalculator
{
    public function isEnabled(): bool
    {
        return (bool) setting('smart_shipping_enabled');
    }

    public function baseRate(): Money
    {
        $baseRate = (float) (setting('smart_shipping_base_rate') ?? 0);

        return Money::inDefaultCurrency($baseRate);
    }

    public function freeThreshold(): ?Money
    {
        $threshold = (float) (setting('smart_shipping_free_threshold') ?? 0);

        if ($threshold <= 0) {
            return null;
        }

        return Money::inDefaultCurrency($threshold);
    }

    public function costForSubtotal(Money $subTotal): Money
    {
        $baseRate = $this->baseRate();
        $threshold = $this->freeThreshold();

        if ($threshold === null) {
            return $baseRate;
        }

        if ($subTotal->greaterThanOrEqual($threshold)) {
            return Money::inDefaultCurrency(0);
        }

        return $baseRate;
    }

    public function costForCurrentCart(): Money
    {
        return $this->costForSubtotal(Cart::subTotal());
    }
}
