<?php

declare(strict_types=1);

namespace Cartino\Events\Payments;

use Cartino\Events\Event;

class PaymentMethodAdded extends Event
{
    public function __construct($customer, $paymentMethod)
    {
        parent::__construct([
            'customer' => $customer,
            'payment_method' => $paymentMethod,
            'entry' => $paymentMethod,
            'type' => 'payment_method_added',
            'timestamp' => time(),
        ]);
    }

    public function customer()
    {
        return $this->get('customer');
    }

    public function paymentMethod()
    {
        return $this->get('payment_method');
    }
}
