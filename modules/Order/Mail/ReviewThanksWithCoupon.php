<?php

namespace Modules\Order\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Media\Entities\File;
use Modules\Order\Entities\Order;
use Modules\Review\Entities\Review;
use Modules\Coupon\Entities\Coupon;

class ReviewThanksWithCoupon extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Order $order;
    public Review $review;
    public Coupon $coupon;

    public function __construct(Order $order, Review $review, Coupon $coupon)
    {
        app()->setLocale($order->locale);
        $this->order = $order;
        $this->review = $review;
        $this->coupon = $coupon;
    }

    public function build()
    {
        $raw = $this->coupon->value;
        $percent = is_numeric($raw) ? (int) round($raw) : (int) $raw;
        $subject = setting('store_name') . ' – ' . '❤️ Yorumunuz için teşekkürler! %' . $percent . ' indirim kuponunuz hazır';
        return $this->subject($subject)
            ->view('storefront::emails.' . $this->getViewName(), [
                'logo' => File::findOrNew(setting('storefront_mail_logo'))->path,
                'order' => $this->order,
                'review' => $this->review,
                'coupon' => $this->coupon,
            ]);
    }

    private function getViewName(): string
    {
        return 'review_thanks_coupon' . (is_rtl() ? '_rtl' : '');
    }
}
