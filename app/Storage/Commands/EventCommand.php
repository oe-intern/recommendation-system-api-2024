<?php

namespace App\Storage\Commands;

use App\Collections\EventCollection;
use App\Contracts\Commands\IEventCommand;
use App\Contracts\Queries\IProductQuery;
use App\Objects\Enums\EventType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EventCommand implements IEventCommand
{
    /**
     * @var IProductQuery
     */
    protected IProductQuery $productQuery;

    /**
     * EventCommand constructor.
     *
     * @param IProductQuery $productQuery
     */
    public function __construct(IProductQuery $productQuery)
    {
        $this->productQuery = $productQuery;
    }

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
        ?int $quantity
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

    /**
     * Increment the number of clicks on a product from a shop.
     *
     * @param string $shopId
     * @param string $productId
     * @param mixed $data
     * @return void
     *
     * @throws ModelNotFoundException
     */
    public function incrementClicks(string $shopId, string $productId, mixed $data): void
    {
        EventCollection::query()->create([
            'type' => EventType::CLICK,
            'shop_id' => $shopId,
            'product_id' => $productId,
            'created_at' => Carbon::now(),
            'data' => $data,
        ]);
    }

    /**
     * Increase the number of times a product is added to cart.
     *
     * @param string $shopId
     * @param string $productId
     * @param mixed $data
     * @param int|null $quantity
     * @return void
     */
    public function incrementAddToCart(string $shopId, string $productId, mixed $data, ?int $quantity = 1): void
    {
        $quantity = $quantity ?? 1;
        EventCollection::query()
            ->create([
                'type' => EventType::ADD_TO_CART,
                'shop_id' => $shopId,
                'product_id' => $productId,
                'created_at' => Carbon::now(),
                'quantity' => $quantity,
                'data' => $data,
            ]);
    }
}
