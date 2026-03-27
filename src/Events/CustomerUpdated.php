<?php

declare(strict_types=1);

namespace Cartino\Events;

class CustomerUpdated extends Event
{
    public function __construct($customer, $original = null)
    {
        parent::__construct([
            'customer' => $customer,
            'original' => $original,
            'entry' => $customer,
            'email' => $customer->get('email'),
            'name' => $customer->get('name'),
            'type' => 'customer_updated',
            'timestamp' => time(),
        ]);
    }

    public function customer()
    {
        return $this->get('customer');
    }

    public function original()
    {
        return $this->get('original');
    }

    public function email()
    {
        return $this->get('email');
    }

    public function name()
    {
        return $this->get('name');
    }
}
