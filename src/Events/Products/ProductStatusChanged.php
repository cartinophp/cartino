<?php

declare(strict_types=1);

namespace Cartino\Events\Products;

use Cartino\Events\Event;

class ProductStatusChanged extends Event
{
    public function __construct($product, string $previousStatus, string $newStatus)
    {
        parent::__construct([
            'product' => $product,
            'entry' => $product,
            'sku' => $product->get('sku'),
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'type' => 'product_status_changed',
            'timestamp' => time(),
        ]);
    }

    public function product()
    {
        return $this->get('product');
    }

    public function previousStatus()
    {
        return $this->get('previous_status');
    }

    public function newStatus()
    {
        return $this->get('new_status');
    }

    public function isPublished(): bool
    {
        return $this->newStatus() === 'active';
    }

    public function isDrafted(): bool
    {
        return $this->newStatus() === 'draft';
    }

    public function isArchived(): bool
    {
        return $this->newStatus() === 'archived';
    }
}
