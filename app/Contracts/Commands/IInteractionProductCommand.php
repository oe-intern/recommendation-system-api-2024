<?php

namespace App\Contracts\Commands;

interface IInteractionProductCommand
{
    /**
     * Increment the clicks of a product from a shop.
     *
     * @param string $shop_domain
     * @param string $product_id
     * @param int $quantity
     * @return void
     */
    public function incrementClicks(string $shop_domain, string $product_id, int $quantity = 1): void;

    /**
     * Increment the views of a product from a shop.
     *
     * @param string $shop_domain
     * @param string $product_id
     * @param int $quantity
     * @return void
     */
    public function incrementAddToCart(string $shop_domain, string $product_id, int $quantity = 1): void;
}
