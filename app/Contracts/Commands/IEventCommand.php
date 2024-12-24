<?php

namespace App\Contracts\Commands;

use App\Objects\Enums\EventType;

interface IEventCommand
{
    /**
     * Trigger any event of a product from a shop.
     *
     * @param string $shopId
     * @param string $productId
     * @param EventType $eventType
     * @param mixed $data
     * @param int|null $quantity
     * @return void
     */
    public function trigger(
        string $shopId,
        string $productId,
        EventType $eventType,
        mixed $data,
        ?int $quantity,
    ): void;

    /**
     * Increment the clicks of a product from a shop.
     *
     * @param string $shopId
     * @param string $productId
     * @param mixed $data
     * @return void
     */
    public function incrementClicks(string $shopId, string $productId, mixed $data): void;

    /**
     * Increment the views of a product from a shop.
     *
     * @param string $shopId
     * @param string $productId
     * @param mixed $data
     * @param int|null $quantity
     * @return void
     */
    public function incrementAddToCart(string $shopId, string $productId, mixed $data, ?int $quantity = 1): void;
}
