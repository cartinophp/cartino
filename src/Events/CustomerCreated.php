<?php

declare(strict_types=1);

namespace Cartino\Events;

class CustomerCreated extends Event
{
    public function __construct($customer)
    {
        parent::__construct([
            'customer' => $customer,
            'entry' => $customer,
            'email' => $customer->get('email'),
            'name' => $customer->get('name'),
            'type' => 'customer_created',
            'timestamp' => time(),
        ]);
    }

    public function customer()
    {
        return $this->get('customer');
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
