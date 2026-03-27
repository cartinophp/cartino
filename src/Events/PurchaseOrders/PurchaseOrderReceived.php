<?php

declare(strict_types=1);

namespace Cartino\Events\PurchaseOrders;

use Cartino\Events\Event;

class PurchaseOrderReceived extends Event
{
    public function __construct($purchaseOrder, bool $isPartial = false)
    {
        parent::__construct([
            'purchase_order' => $purchaseOrder,
            'entry' => $purchaseOrder,
            'is_partial' => $isPartial,
            'type' => $isPartial ? 'purchase_order_partially_received' : 'purchase_order_received',
            'timestamp' => time(),
        ]);
    }

    public function purchaseOrder()
    {
        return $this->get('purchase_order');
    }

    public function isPartial(): bool
    {
        return (bool) $this->get('is_partial');
    }
}
