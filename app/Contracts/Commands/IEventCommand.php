<?php

namespace App\Contracts\Commands;

use App\Objects\Enums\EventType;

interface IEventCommand
{
    /**
     * Trigger any event of a product from a shop.
     *
     * @param string $shop_id
     * @param string $product_id
     * @param EventType $event_type
     * @param mixed $data
     * @param int|null $quantity
     * @return void
     */
    public function trigger(
        string $shop_id,
        string $product_id,
        EventType $event_type,
        mixed $data,
        ?int $quantity,
    ): void;

    /**
     * Increment the clicks of a product from a shop.
     *
     * @param string $shop_id
     * @param string $product_id
     * @param mixed $data
     * @return void
     */
    public function incrementClicks(string $shop_id, string $product_id, mixed $data): void;

    /**
     * Increment the views of a product from a shop.
     *
     * @param string $shop_id
     * @param string $product_id
     * @param mixed $data
     * @param int|null $quantity
     * @return void
     */
    public function incrementAddToCart(string $shop_id, string $product_id, mixed $data, ?int $quantity = 1): void;
}
