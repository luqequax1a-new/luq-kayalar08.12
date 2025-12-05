<?php

namespace Modules\Review\Events;

use Modules\Review\Entities\Review;

class ReviewCreated
{
    public Review $review;

    public function __construct(Review $review)
    {
        $this->review = $review;
    }
}

