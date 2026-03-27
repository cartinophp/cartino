<?php

declare(strict_types=1);

namespace Cartino\Events\Customers;

use Cartino\Events\Event;

class CustomerAddressUpdated extends Event
{
    public function __construct($customer, $address, $original = null)
    {
        parent::__construct([
            'customer' => $customer,
            'address' => $address,
            'original' => $original,
            'entry' => $customer,
            'type' => 'customer_address_updated',
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

    public function original()
    {
        return $this->get('original');
    }
}
