<?php

namespace Modules\Coupon\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Modules\Coupon\Entities\Coupon;
use Modules\Admin\Traits\HasCrudActions;
use Modules\Coupon\Admin\ReviewCouponTable;

class ReviewCouponController
{
    use HasCrudActions;

    protected $model = Coupon::class;
    protected $viewPath = 'coupon::admin.review_coupons';

    public function table(Request $request)
    {
        $query = Coupon::query()
            ->withoutGlobalScope('active')
            ->where('is_review_coupon', true)
            ->with(['order']);

        return new ReviewCouponTable($query);
    }
}
