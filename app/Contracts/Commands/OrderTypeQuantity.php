<?php

namespace App\Contracts\Commands;

use App\Collections\Shop as ShopCollection;

interface OrderTypeQuantity
{
    /**
     * Increment the number of product types from a shop.
     *
     * @param ShopCollection $shop
     * @param string $order_type
     * @param int $quantity
     * @return void
     */
    public function increment(ShopCollection $shop, string $order_type, int $quantity = 1): void;
}
