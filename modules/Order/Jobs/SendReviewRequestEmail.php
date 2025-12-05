<?php

namespace Modules\Order\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Modules\Order\Entities\Order;
use Modules\Order\Mail\ReviewRequest;

class SendReviewRequestEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle(): void
    {
        $order = $this->order->fresh(['products.product.files', 'products.product_variant.files']);

        if ($order->status !== Order::COMPLETED) {
            return;
        }

        if (! empty($order->review_request_sent_at)) {
            return;
        }

        Mail::to($order->customer_email)->send(new ReviewRequest($order));

        $order->forceFill(['review_request_sent_at' => now()])->save();
    }
}

