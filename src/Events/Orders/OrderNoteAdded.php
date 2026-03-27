<?php

declare(strict_types=1);

namespace Cartino\Events\Orders;

use Cartino\Events\Event;

class OrderNoteAdded extends Event
{
    public function __construct($order, string $note, bool $isCustomerVisible = false)
    {
        parent::__construct([
            'order' => $order,
            'entry' => $order,
            'order_number' => $order->get('order_number'),
            'note' => $note,
            'is_customer_visible' => $isCustomerVisible,
            'type' => 'order_note_added',
            'timestamp' => time(),
        ]);
    }

    public function order()
    {
        return $this->get('order');
    }

    public function orderNumber()
    {
        return $this->get('order_number');
    }

    public function note()
    {
        return $this->get('note');
    }

    public function isCustomerVisible(): bool
    {
        return (bool) $this->get('is_customer_visible');
    }
}
