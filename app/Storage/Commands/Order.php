<?php

namespace App\Storage\Commands;

use App\Collections\Shop as ShopCollection;
use App\Contracts\Commands\Order as OrderCommand;

class Order implements OrderCommand
{
    /**
     * Create list order of a shop with data from Shopify.
     *
     * @param array $order
     * @param ShopCollection $shop
     * @return void
     */
    public function create(array $order, ShopCollection $shop): void
    {
        $shop->orders()->create($order);
    }

    /**
     * Create a list orders of a shop with data from Shopify.
     *
     * @param array $orders
     * @param ShopCollection $shop
     * @return void
     */
    public function createMany(array $orders, ShopCollection $shop): void
    {
        $shop->orders()->createMany($orders);
    }

    /**
     * Update list order of a shop with data from Shopify.
     *
     * @param array $order
     * @param ShopCollection $shop
     * @return bool
     */
    public function update(array $order, ShopCollection $shop): bool
    {
        $existing_order = $shop->orders()->find($order['id']);

        if ($existing_order) {
            return $existing_order->update($order);
        }

        return false;
    }
}
