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
}
