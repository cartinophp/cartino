<?php

declare(strict_types=1);

namespace Cartino\Events\Wishlist;

use Cartino\Events\Event;

class WishlistItemRemoved extends Event
{
    public function __construct($wishlist, $product, $customer)
    {
        parent::__construct([
            'wishlist' => $wishlist,
            'product' => $product,
            'customer' => $customer,
            'entry' => $wishlist,
            'type' => 'wishlist_item_removed',
            'timestamp' => time(),
        ]);
    }

    public function wishlist()
    {
        return $this->get('wishlist');
    }

    public function product()
    {
        return $this->get('product');
    }

    public function customer()
    {
        return $this->get('customer');
    }
}
