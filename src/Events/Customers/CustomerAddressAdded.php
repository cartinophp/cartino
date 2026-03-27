<?php

declare(strict_types=1);

namespace Cartino\Events\Customers;

use Cartino\Events\Event;

class CustomerAddressAdded extends Event
{
    public function __construct($customer, $address)
    {
        parent::__construct([
            'customer' => $customer,
            'address' => $address,
            'entry' => $customer,
            'type' => 'customer_address_added',
            'timestamp' => time(),
        ]);
    }

    public function customer()
    {
        return $this->get('customer');
    }

    public function address()
    {
        return $this->get('address');
    }
}
