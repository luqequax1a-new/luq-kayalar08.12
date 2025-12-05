<?php

namespace Modules\Order\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Media\Entities\File;
use Modules\Order\Entities\Order;

class ReviewRequestSecond extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        app()->setLocale($order->locale);
        $this->order = $order;
    }

    public function build()
    {
        $title = 'Değerlendirme davetimizi kaçırmış olabilirsiniz';

        return $this->subject(setting('store_name') . ' – ' . $title)
            ->view('storefront::emails.review_request_second', [
                'logo'  => File::findOrNew(setting('storefront_mail_logo'))->path,
                'order' => $this->order,
                'title' => $title,
            ]);
    }
}

