<?php

declare(strict_types=1);

namespace Cartino\Events\Subscriptions;

use Cartino\Events\Event;

class SubscriptionCreated extends Event
{
    public function __construct($subscription, $customer)
    {
        parent::__construct([
            'subscription' => $subscription,
            'customer' => $customer,
            'entry' => $subscription,
            'type' => 'subscription_created',
            'timestamp' => time(),
        ]);
    }

    public function subscription()
    {
        return $this->get('subscription');
    }

    public function customer()
    {
        return $this->get('customer');
    }
}
