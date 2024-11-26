<?php

namespace App\Contracts\Commands;

use Illuminate\Database\Eloquent\ModelNotFoundException;

interface OrderTypeQuantity
{
    /**
     * Increment the number of product types from a shop.
     *
     * @param string $shop_domain
     * @param string $order_type
     * @param int $quantity
     * @return void
     *
     * @throws ModelNotFoundException
     */
    public function increment(string $shop_domain, string $order_type, int $quantity = 1): void;
}
