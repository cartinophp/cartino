<?php

declare(strict_types=1);

namespace Cartino\Events\Cart;

use Cartino\Events\Event;

class CartConverted extends Event
{
    public function __construct($cart, $order)
    {
        parent::__construct([
            'cart' => $cart,
            'order' => $order,
            'entry' => $order,
            'order_number' => $order->get('order_number'),
            'type' => 'cart_converted',
            'timestamp' => time(),
        ]);
    }

    public function cart()
    {
        return $this->get('cart');
    }

    public function order()
    {
        return $this->get('order');
    }

    public function orderNumber()
    {
        return $this->get('order_number');
    }
}
