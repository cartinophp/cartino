<?php

declare(strict_types=1);

namespace Cartino\Events\Fidelity;

use Cartino\Events\Event;

class FidelityLevelChanged extends Event
{
    public function __construct($customer, string $previousLevel, string $newLevel)
    {
        parent::__construct([
            'customer' => $customer,
            'entry' => $customer,
            'previous_level' => $previousLevel,
            'new_level' => $newLevel,
            'type' => 'fidelity_level_changed',
            'timestamp' => time(),
        ]);
    }

    public function customer()
    {
        return $this->get('customer');
    }

    public function previousLevel()
    {
        return $this->get('previous_level');
    }

    public function newLevel()
    {
        return $this->get('new_level');
    }
}
