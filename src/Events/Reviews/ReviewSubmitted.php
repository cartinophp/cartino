<?php

declare(strict_types=1);

namespace Cartino\Events\Reviews;

use Cartino\Events\Event;

class ReviewSubmitted extends Event
{
    public function __construct($review, $product, $customer)
    {
        parent::__construct([
            'review' => $review,
            'product' => $product,
            'customer' => $customer,
            'entry' => $review,
            'rating' => $review->get('rating'),
            'type' => 'review_submitted',
            'timestamp' => time(),
        ]);
    }

    public function review()
    {
        return $this->get('review');
    }

    public function product()
    {
        return $this->get('product');
    }

    public function customer()
    {
        return $this->get('customer');
    }

    public function rating()
    {
        return $this->get('rating');
    }
}
