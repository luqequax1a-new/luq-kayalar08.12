<?php

namespace Modules\Order\Listeners;

use Illuminate\Support\Facades\Mail;
use Modules\Order\Events\OrderStatusChanged;
use Modules\Order\Mail\OrderStatusChanged as OrderStatusChangedEmail;

class SendOrderStatusChangedEmail
{
    /**
     * Handle the event.
     *
     * @param OrderStatusChanged $event
     *
     * @return void
     */
    public function handle(OrderStatusChanged $event)
    {
        $codLabel = (string) setting('cod_label');
        if ($codLabel !== '' && (string) $event->order->payment_method === $codLabel) {
            if ($event->order->status !== \Modules\Order\Entities\Order::SHIPPED) {
                return;
            }
        }

        $statuses = setting('email_order_statuses', []);
        if (!in_array($event->order->status, $statuses) && $event->order->status !== \Modules\Order\Entities\Order::SHIPPED) {
            return;
        }

        Mail::to($event->order->customer_email)
            ->send(new OrderStatusChangedEmail($event->order));
    }
}
