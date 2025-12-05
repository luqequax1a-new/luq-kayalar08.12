<?php

namespace Modules\Review\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Review\Events\ReviewCreated;
use Modules\Review\Listeners\CreateReviewCouponAndSendMail;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        ReviewCreated::class => [
            CreateReviewCouponAndSendMail::class,
        ],
    ];
}

