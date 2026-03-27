<?php

declare(strict_types=1);

namespace Cartino\Events\PurchaseOrders;

use Cartino\Events\Event;

class PurchaseOrderSent extends Event
{
    public function __construct($purchaseOrder)
    {
        parent::__construct([
            'purchase_order' => $purchaseOrder,
            'entry' => $purchaseOrder,
            'type' => 'purchase_order_sent',
            'timestamp' => time(),
        ]);
    }

    public function purchaseOrder()
    {
        return $this->get('purchase_order');
    }
}
