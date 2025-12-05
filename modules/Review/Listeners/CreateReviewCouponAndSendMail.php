<?php

namespace Modules\Review\Listeners;

use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Modules\Order\Entities\Order;
use Modules\Coupon\Entities\Coupon;
use Modules\Review\Events\ReviewCreated;
use Illuminate\Support\Facades\Mail;
use Modules\Order\Mail\ReviewThanksWithCoupon;

class CreateReviewCouponAndSendMail
{
    public function handle(ReviewCreated $event): void
    {
        if (! setting('review_coupon_enabled', true)) {
            return;
        }
        $review = $event->review;

        // Kupon sadece belirli bir siparişe bağlı yorumlar için üretilir.
        // review->order_id boş ise kupon oluşturulmaz.
        $orderId = $review->order_id;
        if (empty($orderId)) {
            return;
        }

        $orderQuery = Order::query()
            ->where('id', $orderId)
            ->where('status', Order::COMPLETED);

        // Eğer reviewer_id varsa, siparişin bu kullanıcıya ait olduğundan da emin ol
        if (! empty($review->reviewer_id)) {
            $orderQuery->where('customer_id', $review->reviewer_id);
        }

        $order = $orderQuery->first();

        if (! $order) {
            return;
        }

        $customerId = $order->customer_id;

        $exists = Coupon::query()
            ->where('is_review_coupon', true)
            ->where('order_id', $order->id)
            ->where('review_id', $review->id)
            ->exists();

        if ($exists) {
            return;
        }

        $discount = (int) (setting('review_coupon_discount_percent', 10) ?: 10);
        $validDays = (int) (setting('review_coupon_valid_days', 30) ?: 30);

        $code = sprintf('RVW-%d-%s', $review->id, Str::upper(Str::random(4)));

        $coupon = Coupon::create([
            'name' => 'Review Coupon',
            'code' => $code,
            'is_percent' => true,
            'value' => $discount,
            'free_shipping' => false,
            'start_date' => Carbon::now()->toDateString(),
            'end_date' => Carbon::now()->addDays(max(1, $validDays))->toDateString(),
            'is_active' => true,
            'usage_limit_per_coupon' => 1,
            'customer_id' => $customerId,
            'order_id' => $order->id,
            'review_id' => $review->id,
            'is_review_coupon' => true,
        ]);

        Mail::to($order->customer_email)->send(new ReviewThanksWithCoupon($order, $review, $coupon));
    }
}
