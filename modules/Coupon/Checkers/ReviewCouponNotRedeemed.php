<?php

namespace Modules\Coupon\Checkers;

use Closure;
use Modules\Coupon\Exceptions\CouponUsageLimitReachedException;

class ReviewCouponNotRedeemed
{
    public function handle($coupon, Closure $next)
    {
        if ($coupon && $coupon->is_review_coupon) {
            if ($coupon->redeemed_at || !$coupon->is_active) {
                throw new CouponUsageLimitReachedException;
            }
        }

        return $next($coupon);
    }
}

