<?php

namespace App\Storage\Commands;

use App\Collections\Schema\OrderTypeQuantity as OrderTypeQuantitySchema;
use App\Collections\Shop as ShopCollection;
use App\Contracts\Commands\OrderTypeQuantity as OrderTypeQuantityCommand;

class OrderTypeQuantity implements OrderTypeQuantityCommand
{
    /**
     * Increment the number of product types from a shop.
     *
     * @param ShopCollection $shop
     * @param string $order_type
     * @param int $quantity
     * @return void
     */
    public function increment(ShopCollection $shop, string $order_type, int $quantity = 1): void
    {
        $order_type_quantity = $shop->orderTypeQuantities()->where('type', $order_type)->first();

        if ($order_type_quantity) {
            $order_type_quantity->quantity += $quantity;
        } else {
            $order_type_quantity = new OrderTypeQuantitySchema([
                'type' => $order_type,
                'quantity' => $quantity,
            ]);
        }

        $shop->orderTypeQuantities()->save($order_type_quantity);
    }
}
