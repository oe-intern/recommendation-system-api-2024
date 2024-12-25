<?php

namespace App\Storage\Commands;

use App\Collections\EventCollection;
use App\Contracts\Commands\IEventCommand;
use App\Objects\Enums\EventType;
use Carbon\Carbon;

class EventCommand implements IEventCommand
{
    /**
     * Increment any event of a product from a shop.
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
    ): void {
        $quantity = $quantity ?? 1;
        EventCollection::query()
            ->create([
                'type' => $eventType->value,
                'shop_id' => $shopId,
                'product_id' => $productId,
                'created_at' => Carbon::now(),
                'quantity' => $quantity,
                'data' => $data,
            ]);
    }
}
