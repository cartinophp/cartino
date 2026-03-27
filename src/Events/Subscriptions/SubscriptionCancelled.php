<?php

declare(strict_types=1);

namespace Cartino\Events\Subscriptions;

use Cartino\Events\Event;

class SubscriptionCancelled extends Event
{
    public function __construct($subscription, ?string $reason = null)
    {
        parent::__construct([
            'subscription' => $subscription,
            'entry' => $subscription,
            'reason' => $reason,
            'type' => 'subscription_cancelled',
            'timestamp' => time(),
        ]);
    }

    public function subscription()
    {
        return $this->get('subscription');
    }

    public function reason()
    {
        return $this->get('reason');
    }
}
