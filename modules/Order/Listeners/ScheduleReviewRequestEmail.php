<?php

namespace Modules\Order\Listeners;

use Illuminate\Support\Facades\Bus;
use Modules\Order\Events\OrderStatusChanged;
use Modules\Order\Jobs\SendReviewRequestEmail;

class ScheduleReviewRequestEmail
{
    public function handle(OrderStatusChanged $event): void
    {
        if (! setting('review_request_enabled', true)) {
            return;
        }
        if ($event->order->status !== \Modules\Order\Entities\Order::COMPLETED) {
            return;
        }

        if (! empty($event->order->review_request_sent_at)) {
            return;
        }

        $delayDays = (int) setting('review_request_delay_days', 7);

        // Negatif / geçersiz değerleri 0'a normalize et
        if ($delayDays < 0) {
            $delayDays = 0;
        }

        if ($delayDays === 0) {
            // 0 gün: sipariş tamamlanınca hemen mail
            Bus::dispatch(new SendReviewRequestEmail($event->order));
            return;
        }

        // 1+ gün: belirtilen gün sayısı kadar gecikmeli gönder
        $delay = now()->addDays($delayDays);
        Bus::dispatch((new SendReviewRequestEmail($event->order))->delay($delay));
    }
}
