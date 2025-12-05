<?php

namespace Modules\Checkout\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Modules\Media\Entities\File;
use Modules\Order\Entities\Order;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewOrder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $order;


    /**
     * Create a new message instance.
     *
     * @param Order $order
     *
     * @return void
     */
    public function __construct($order)
    {
        $this->order = $order;
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        app()->setLocale($this->order->locale);

        $this->order->load([
            'products.product.files',
            'products.product_variant.files',
        ]);

        return $this->subject('Yeni Sipariş 🔔')
            ->view("storefront::emails.{$this->getViewName()}", [
                'logo' => File::findOrNew(setting('storefront_mail_logo'))->path,
            ]);
    }


    private function getViewName()
    {
        return 'new_order' . (is_rtl() ? '_rtl' : '');
    }
}
