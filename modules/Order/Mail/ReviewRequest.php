<?php

namespace Modules\Order\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Modules\Media\Entities\File;
use Modules\Order\Entities\Order;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReviewRequest extends Mailable implements ShouldQueue
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
        $title = setting('review_request_email_title') ?: trans('storefront::product.add_a_review');
        $intro = setting('review_request_email_intro') ?: 'Siparişiniz elinize ulaştı. Deneyiminizi bizimle ve diğer müşterilerimizle paylaşır mısınız?';
        $promo = setting('review_request_email_promo') ?: 'Siparişiniz hakkında değerlendirme yapmayı unutmayın. Yorum bırakan müşterilerimize bir sonraki alışverişlerinde kullanmaları için özel indirim kuponu tanımlıyoruz.';

        return $this->subject(setting('store_name') . ' – ' . $title)
            ->view("storefront::emails." . $this->getViewName(), [
                'logo' => File::findOrNew(setting('storefront_mail_logo'))->path,
                'order' => $this->order,
                'title' => $title,
                'intro' => $intro,
                'promo' => $promo,
            ]);
    }

    private function getViewName()
    {
        return 'review_request' . (is_rtl() ? '_rtl' : '');
    }
}
