<?php

namespace App\Storage\Commands;

use App\Collections\Schema\OrderTypeQuantity as OrderTypeQuantitySchema;
use App\Contracts\Commands\OrderTypeQuantity as OrderTypeQuantityCommand;
use App\Collections\Shop as ShopCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OrderTypeQuantity implements OrderTypeQuantityCommand
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
    public function increment(string $shop_domain, string $order_type, int $quantity = 1): void
    {
        $order_type_quantity = ShopCollection::query()
            ->where('domain', $shop_domain)
            ->firstOrFail()
            ->orderTypeQuantities()
            ->where('type', $order_type)
            ->first();

        if ($order_type_quantity) {
            $order_type_quantity->quantity += $quantity;
        } else {
            $order_type_quantity = new OrderTypeQuantitySchema([
                'type' => $order_type,
                'quantity' => $quantity,
            ]);
        }

        $order_type_quantity->save();
    }
}
