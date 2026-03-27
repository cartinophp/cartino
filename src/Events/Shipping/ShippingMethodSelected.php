<?php

declare(strict_types=1);

namespace Cartino\Events\Shipping;

use Cartino\Events\Event;

class ShippingMethodSelected extends Event
{
    public function __construct($cart, $shippingMethod)
    {
        parent::__construct([
            'cart' => $cart,
            'shipping_method' => $shippingMethod,
            'entry' => $cart,
            'type' => 'shipping_method_selected',
            'timestamp' => time(),
        ]);
    }

    public function cart()
    {
        return $this->get('cart');
    }

    public function shippingMethod()
    {
        return $this->get('shipping_method');
    }
}
