<?php

namespace Modules\Checkout\Listeners;

use Exception;
use Modules\Checkout\Mail\Invoice;
use Modules\Checkout\Mail\NewOrder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Modules\Checkout\Events\OrderPlaced;

class SendNewOrderEmails
{
    /**
     * Handle the event.
     *
     * @param OrderPlaced $event
     *
     * @return void
     */
    public function handle(OrderPlaced $event)
    {
        Log::channel('stack')->info('order_placed_email_start', [
            'order_id' => $event->order->id,
            'admin_enabled' => (bool) setting('admin_order_email'),
            'invoice_enabled' => (bool) setting('invoice_email'),
            'store_email' => setting('store_email'),
            'customer_email' => $event->order->customer_email,
        ]);

        if (setting('admin_order_email')) {
            try {
                Mail::to(setting('store_email'))
                    ->send(new NewOrder($event->order));
                Log::channel('stack')->info('order_placed_email_admin_sent', [
                    'order_id' => $event->order->id,
                ]);
            } catch (Exception $e) {
                Log::channel('stack')->error('order_placed_email_admin_error', [
                    'order_id' => $event->order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if (setting('invoice_email')) {
            try {
                Mail::to($event->order->customer_email)
                    ->send(new Invoice($event->order));
                Log::channel('stack')->info('order_placed_email_invoice_sent', [
                    'order_id' => $event->order->id,
                ]);
            } catch (Exception $e) {
                Log::channel('stack')->error('order_placed_email_invoice_error', [
                    'order_id' => $event->order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
