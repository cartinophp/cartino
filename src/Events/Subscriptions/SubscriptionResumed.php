<?php

declare(strict_types=1);

namespace Cartino\Events\Subscriptions;

use Cartino\Events\Event;

class SubscriptionResumed extends Event
{
    public function __construct($subscription)
    {
        parent::__construct([
            'subscription' => $subscription,
            'entry' => $subscription,
            'type' => 'subscription_resumed',
            'timestamp' => time(),
        ]);
    }

    public function subscription()
    {
        return $this->get('subscription');
    }
}
