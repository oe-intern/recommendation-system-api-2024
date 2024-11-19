<?php

namespace App\Contracts\Commands;

use App\Collections\Shop as ShopCollection;

interface InteractionProduct
{
    /**
     * Increment the clicks of a product from a shop.
     *
     * @param ShopCollection $shop
     * @param string $product_id
     * @param int $quantity
     * @return void
     */
    public function incrementClicks(ShopCollection $shop, string $product_id, int $quantity = 1): void;

    /**
     * Increment the views of a product from a shop.
     *
     * @param ShopCollection $shop
     * @param string $product_id
     * @param int $quantity
     * @return void
     */
    public function incrementAddToCart(ShopCollection $shop, string $product_id, int $quantity = 1): void;
}
