<?php

namespace App\Storage\Commands;

use App\Collections\ProductAddToCartCollection;
use App\Collections\ProductClickCollection;
use App\Contracts\Commands\IInteractionProductCommand;
use App\Contracts\Queries\IProductQuery;
use App\Objects\Enums\InteractionType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class InteractionProductCommand implements IInteractionProductCommand
{
    /**
     * @var IProductQuery
     */
    protected IProductQuery $product_query;

    /**
     * InteractionProductCommand constructor.
     *
     * @param IProductQuery $product_query
     */
    public function __construct(IProductQuery $product_query)
    {
        $this->product_query = $product_query;
    }

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
    ): void {
        match ($interaction_type) {
            InteractionType::CLICK => $this->incrementClicks($shop_id, $product_id, $quantity),
            InteractionType::ADD_TO_CART => $this->incrementAddToCart($shop_id, $product_id, $quantity),
        };
    }

    /**
     * Increment the number of clicks on a product from a shop.
     *
     * @param string $shop_id
     * @param string $product_id
     * @return void
     *
     * @throws ModelNotFoundException
     */
    public function incrementClicks(string $shop_id, string $product_id): void
    {
        ProductClickCollection::query()->create([
            'shop_id' => $shop_id,
            'product_id' => $product_id,
            'created_at' => Carbon::now(),
        ]);
    }

    /**
     * Increase the number of times a product is added to cart.
     *
     * @param string $shop_id
     * @param string $product_id
     * @param int|null $quantity
     * @return void
     */
    public function incrementAddToCart(string $shop_id, string $product_id, ?int $quantity = 1): void
    {
        $quantity = $quantity ?? 1;
        ProductAddToCartCollection::query()
            ->create([
                'shop_id' => $shop_id,
                'product_id' => $product_id,
                'created_at' => Carbon::now(),
                'quantity' => $quantity,
            ]);
    }
}
