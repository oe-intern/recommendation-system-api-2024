<?php

namespace App\Contracts\Commands;

use App\Collections\Shop as ShopCollection;

interface Order
{
    /**
     * Create list order of a shop with data from Shopify.
     *
     * @param array $orders
     * @param ShopCollection $shop
     * @return void
     */
    public function createMany(array $orders, ShopCollection $shop): void;

    /**
     * Create a single order of a shop with data from Shopify.
     *
     * @param array $order
     * @param ShopCollection $shop
     * @return void
     */
    public function create(array $order, ShopCollection $shop): void;

    /**
     * Update list order of a shop with data from Shopify.
     *
     * @param array $order
     * @param ShopCollection $shop
     * @return bool
     */
    public function update(array $order, ShopCollection $shop): bool;
}
