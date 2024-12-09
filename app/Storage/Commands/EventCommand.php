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
    protected IProductQuery $product_query;

    /**
     * EventCommand constructor.
     *
     * @param IProductQuery $product_query
     */
    public function __construct(IProductQuery $product_query)
    {
        $this->product_query = $product_query;
    }

    /**
     * Increment any event of a product from a shop.
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
        ?int $quantity
    ): void {
        $quantity = $quantity ?? 1;
        EventCollection::query()
            ->create([
                'type' => $event_type->value,
                'shop_id' => $shop_id,
                'product_id' => $product_id,
                'created_at' => Carbon::now(),
                'quantity' => $quantity,
                'data' => $data,
            ]);
    }

    /**
     * Increment the number of clicks on a product from a shop.
     *
     * @param string $shop_id
     * @param string $product_id
     * @param mixed $data
     * @return void
     *
     * @throws ModelNotFoundException
     */
    public function incrementClicks(string $shop_id, string $product_id, mixed $data): void
    {
        EventCollection::query()->create([
            'type' => EventType::CLICK,
            'shop_id' => $shop_id,
            'product_id' => $product_id,
            'created_at' => Carbon::now(),
            'data' => $data,
        ]);
    }

    /**
     * Increase the number of times a product is added to cart.
     *
     * @param string $shop_id
     * @param string $product_id
     * @param mixed $data
     * @param int|null $quantity
     * @return void
     */
    public function incrementAddToCart(string $shop_id, string $product_id, mixed $data, ?int $quantity = 1): void
    {
        $quantity = $quantity ?? 1;
        EventCollection::query()
            ->create([
                'type' => EventType::ADD_TO_CART,
                'shop_id' => $shop_id,
                'product_id' => $product_id,
                'created_at' => Carbon::now(),
                'quantity' => $quantity,
                'data' => $data,
            ]);
    }
}
