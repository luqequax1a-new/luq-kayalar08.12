<?php

namespace Modules\Coupon\Checkers;

use Closure;
use Illuminate\Support\Facades\Auth;
use Modules\Coupon\Exceptions\InapplicableCouponException;

class SpecificCustomer
{
    public function handle($coupon, Closure $next)
    {
        if (!empty($coupon->customer_id)) {
            if (!Auth::check() || Auth::id() !== (int) $coupon->customer_id) {
                throw new InapplicableCouponException;
            }
        }

        return $next($coupon);
    }
}

