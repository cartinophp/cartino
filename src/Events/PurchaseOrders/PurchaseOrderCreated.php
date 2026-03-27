<?php

declare(strict_types=1);

namespace Cartino\Events\PurchaseOrders;

use Cartino\Events\Event;

class PurchaseOrderCreated extends Event
{
    public function __construct($purchaseOrder, $supplier)
    {
        parent::__construct([
            'purchase_order' => $purchaseOrder,
            'supplier' => $supplier,
            'entry' => $purchaseOrder,
            'type' => 'purchase_order_created',
            'timestamp' => time(),
        ]);
    }

    public function purchaseOrder()
    {
        return $this->get('purchase_order');
    }

    public function supplier()
    {
        return $this->get('supplier');
    }
}
