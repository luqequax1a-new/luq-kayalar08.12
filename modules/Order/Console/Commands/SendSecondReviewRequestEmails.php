<?php

namespace Modules\Order\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Modules\Order\Entities\Order;
use Modules\Review\Entities\Review;
use Modules\Order\Mail\ReviewRequestSecond;

class SendSecondReviewRequestEmails extends Command
{
    protected $signature = 'order:send-second-review-requests';
    protected $description = 'İlk yorum davet mailine yanıt vermeyen müşterilere ikinci hatırlatma gönderir';

    public function handle(): int
    {
        $delayDays = (int) setting('review_request_second_delay_days', 5);

        // En az 1 gün olsun; 0 / negatif / geçersiz değerler için 1 güne çek
        if ($delayDays < 1) {
            $delayDays = 1;
        }

        $cutoff = now()->subDays($delayDays);

        $orders = Order::query()
            ->where('status', Order::COMPLETED)
            ->whereNotNull('review_request_sent_at')
            ->whereNull('review_request_second_sent_at')
            ->where('review_request_sent_at', '<=', $cutoff)
            ->get();

        foreach ($orders as $order) {
            $hasAnyReview = Review::query()->where('order_id', $order->id)->exists();
            if ($hasAnyReview) {
                continue;
            }

            Mail::to($order->customer_email)->queue(new ReviewRequestSecond($order));

            $order->forceFill([
                'review_request_second_sent_at' => now(),
            ])->save();

            $this->info("Second review request sent for order #{$order->id}");
        }

        return Command::SUCCESS;
    }
}

