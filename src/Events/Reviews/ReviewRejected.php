<?php

declare(strict_types=1);

namespace Cartino\Events\Reviews;

use Cartino\Events\Event;

class ReviewRejected extends Event
{
    public function __construct($review, ?string $reason = null)
    {
        parent::__construct([
            'review' => $review,
            'entry' => $review,
            'reason' => $reason,
            'type' => 'review_rejected',
            'timestamp' => time(),
        ]);
    }

    public function review()
    {
        return $this->get('review');
    }

    public function reason()
    {
        return $this->get('reason');
    }
}
