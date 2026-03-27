<?php

declare(strict_types=1);

namespace Cartino\Events\PurchaseOrders;

use Cartino\Events\Event;

class PurchaseOrderCancelled extends Event
{
    public function __construct($purchaseOrder, ?string $reason = null)
    {
        parent::__construct([
            'purchase_order' => $purchaseOrder,
            'entry' => $purchaseOrder,
            'reason' => $reason,
            'type' => 'purchase_order_cancelled',
            'timestamp' => time(),
        ]);
    }

    public function purchaseOrder()
    {
        return $this->get('purchase_order');
    }

    public function reason()
    {
        return $this->get('reason');
    }
}
