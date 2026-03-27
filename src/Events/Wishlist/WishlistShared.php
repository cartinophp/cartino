<?php

declare(strict_types=1);

namespace Cartino\Events\Wishlist;

use Cartino\Events\Event;

class WishlistShared extends Event
{
    public function __construct($wishlist, $customer, ?string $sharedWith = null)
    {
        parent::__construct([
            'wishlist' => $wishlist,
            'customer' => $customer,
            'shared_with' => $sharedWith,
            'entry' => $wishlist,
            'type' => 'wishlist_shared',
            'timestamp' => time(),
        ]);
    }

    public function wishlist()
    {
        return $this->get('wishlist');
    }

    public function customer()
    {
        return $this->get('customer');
    }

    public function sharedWith()
    {
        return $this->get('shared_with');
    }
}
