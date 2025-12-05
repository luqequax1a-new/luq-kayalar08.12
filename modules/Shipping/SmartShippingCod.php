<?php

namespace Modules\Shipping;

use Modules\Cart\Facades\Cart;
use Modules\Support\Money;

class SmartShippingCod
{
    public static function codIsEnabled(): bool
    {
        // COD ücretlerinin devreye girmesi için yalnızca COD'un etkin olması yeterli.
        // Kontrol anahtarı (cod_control_enabled), min/max aralık kurallarını
        // aktive etmek için ayrı bir bayrak olarak kullanılır.
        return (bool) setting('cod_enabled');
    }

    public static function allowedForSubtotal(Money $subTotal): bool
    {
        if (!self::codIsEnabled()) {
            return false;
        }

        // Eğer kontrol anahtarı kapalıysa, COD tüm tutarlar için serbesttir.
        if (!(bool) setting('cod_control_enabled')) {
            return true;
        }

        $min = (float) (setting('cod_min_subtotal') ?? 0);
        $max = (float) (setting('cod_max_subtotal') ?? 0);

        if ($min > 0) {
            $minMoney = Money::inDefaultCurrency($min);
            if ($subTotal->lessThan($minMoney)) {
                return false;
            }
        }

        if ($max > 0) {
            $maxMoney = Money::inDefaultCurrency($max);
            if ($subTotal->greaterThan($maxMoney)) {
                return false;
            }
        }

        return true;
    }

    public static function allowedForCurrentCart(): bool
    {
        return self::allowedForSubtotal(Cart::subTotal());
    }

    public static function codFeeForSubtotal(Money $subTotal): Money
    {
        if (!self::codIsEnabled()) {
            return Money::inDefaultCurrency(0);
        }

        if (!self::allowedForSubtotal($subTotal)) {
            return Money::inDefaultCurrency(0);
        }

        $mode = setting('cod_fee_mode') ?: 'fixed';
        $amount = (float) (setting('cod_fee_amount') ?? 0);
        $percent = (float) (setting('cod_fee_percent') ?? 0);

        if ($mode === 'percent') {
            $fee = $subTotal->multiply($percent / 100)->round();

            return Money::inDefaultCurrency($fee->amount());
        }

        return Money::inDefaultCurrency($amount);
    }

    public static function codFeeForCurrentCart(): Money
    {
        return self::codFeeForSubtotal(Cart::subTotal());
    }

    public static function displayMode(): string
    {
        return setting('cod_fee_display_mode') ?: 'separate_line';
    }
}
