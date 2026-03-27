<?php

declare(strict_types=1);

namespace Cartino\Events\Customers;

use Cartino\Events\Event;

class CustomerEmailVerified extends Event
{
    public function __construct($customer)
    {
        parent::__construct([
            'customer' => $customer,
            'entry' => $customer,
            'email' => $customer->get('email'),
            'type' => 'customer_email_verified',
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
}
