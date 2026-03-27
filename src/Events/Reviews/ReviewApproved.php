<?php

declare(strict_types=1);

namespace Cartino\Events\Reviews;

use Cartino\Events\Event;

class ReviewApproved extends Event
{
    public function __construct($review)
    {
        parent::__construct([
            'review' => $review,
            'entry' => $review,
            'type' => 'review_approved',
            'timestamp' => time(),
        ]);
    }

    public function review()
    {
        return $this->get('review');
    }
}
