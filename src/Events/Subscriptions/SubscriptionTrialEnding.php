<?php

declare(strict_types=1);

namespace Cartino\Events\Subscriptions;

use Cartino\Events\Event;

class SubscriptionTrialEnding extends Event
{
    public function __construct($subscription, int $daysRemaining)
    {
        parent::__construct([
            'subscription' => $subscription,
            'entry' => $subscription,
            'days_remaining' => $daysRemaining,
            'type' => 'subscription_trial_ending',
            'timestamp' => time(),
        ]);
    }

    public function subscription()
    {
        return $this->get('subscription');
    }

    public function daysRemaining()
    {
        return $this->get('days_remaining');
    }
}
