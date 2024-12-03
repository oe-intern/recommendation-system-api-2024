<?php

namespace App\Contracts\Commands;

use App\Objects\Enums\InteractionType;

interface IInteractionProductCommand
{
    /**
     * Increment any interaction of a product from a shop.
     *
     * @param string $shop_id
     * @param string $product_id
     * @param InteractionType $interaction_type
     * @param int|null $quantity
     * @return void
     */
    public function increment(
        string $shop_id,
        string $product_id,
        InteractionType $interaction_type,
        ?int $quantity
    ): void;

    /**
     * Increment the clicks of a product from a shop.
     *
     * @param string $shop_id
     * @param string $product_id
     * @return void
     */
    public function incrementClicks(string $shop_id, string $product_id): void;

    /**
     * Increment the views of a product from a shop.
     *
     * @param string $shop_id
     * @param string $product_id
     * @param int|null $quantity
     * @return void
     */
    public function incrementAddToCart(string $shop_id, string $product_id, ?int $quantity = 1): void;
}
