<?php

declare(strict_types=1);

namespace Cartino\Events\Customers;

use Cartino\Events\Event;

class CustomerGroupAssigned extends Event
{
    public function __construct($customer, $group)
    {
        parent::__construct([
            'customer' => $customer,
            'group' => $group,
            'entry' => $customer,
            'type' => 'customer_group_assigned',
            'timestamp' => time(),
        ]);
    }

    public function customer()
    {
        return $this->get('customer');
    }

    public function group()
    {
        return $this->get('group');
    }
}
