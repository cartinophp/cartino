<?php

declare(strict_types=1);

namespace Cartino\Events\Reviews;

use Cartino\Events\Event;

class ReviewReplied extends Event
{
    public function __construct($review, string $reply)
    {
        parent::__construct([
            'review' => $review,
            'entry' => $review,
            'reply' => $reply,
            'type' => 'review_replied',
            'timestamp' => time(),
        ]);
    }

    public function review()
    {
        return $this->get('review');
    }

    public function reply()
    {
        return $this->get('reply');
    }
}
