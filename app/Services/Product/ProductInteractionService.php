<?php

namespace App\Services\Product;

use App\Contracts\Commands\IInteractionProductCommand;
use App\Contracts\Queries\IInteractionQuery;
use App\Contracts\Queries\IProductQuery;
use App\Contracts\Recommendation\IProductInteraction;
use App\Exceptions\ProductNotFoundException;
use App\Objects\Enums\StatisticsGroupBy;

class ProductInteractionService implements IProductInteraction
{
    /**
     * @var IProductQuery
     */
    protected IProductQuery $product_query;

    /**
     * @var IInteractionProductCommand
     */
    protected IInteractionProductCommand $interaction_product_command;

    /**
     * @var IInteractionQuery
     */
    protected IInteractionQuery $interaction_query;

    /**
     * ProductInteractionService constructor.
     *
     * @param IProductQuery $product_query
     * @param IInteractionProductCommand $interaction_product_command
     * @param IInteractionQuery $interaction_query
     */
    public function __construct(
        IProductQuery $product_query,
        IInteractionProductCommand $interaction_product_command,
        IInteractionQuery $interaction_query
    ) {
        $this->product_query = $product_query;
        $this->interaction_product_command = $interaction_product_command;
        $this->interaction_query = $interaction_query;
    }

    /**
     * Handle click event.
     *
     * @param string $shop_id
     * @param string $product_id
     * @return void
     *
     * @throws ProductNotFoundException
     */
    public function click(string $shop_id, string $product_id): void
    {
        $this->product_query->validateProductId($shop_id, $product_id);

        $this->interaction_product_command->incrementClicks($shop_id, $product_id);
    }

    /**
     * Handle add to cart event.
     *
     * @param string $shop_id
     * @param string $product_id
     * @param int|null $quantity
     * @return void
     *
     * @throws ProductNotFoundException
     */
    public function addToCart(string $shop_id, string $product_id, ?int $quantity): void
    {
        $this->product_query->validateProductId($shop_id, $product_id);

        $this->interaction_product_command->incrementAddToCart($shop_id, $product_id, $quantity);
    }

    /**
     * Get click data.
     *
     * @param string $shop_id
     * @param string|null $product_id
     * @param string $start_date
     * @param string $end_date
     * @param string|null $group_by
     * @return array
     */
    public function getClickData(
        string $shop_id,
        ?string $product_id,
        string $start_date,
        string $end_date,
        ?string $group_by
    ): array {
        $group_by = $group_by ? StatisticsGroupBy::from($group_by) : null;

        return $this->interaction_query->filterClickData($shop_id, $product_id, $start_date, $end_date, $group_by);
    }

    /**
     * Get add to cart data.
     *
     * @param string $shop_id
     * @param string|null $product_id
     * @param string $start_date
     * @param string $end_date
     * @param string|null $group_by
     * @return array
     */
    public function getAddToCartData(
        string $shop_id,
        ?string $product_id,
        string $start_date,
        string $end_date,
        ?string $group_by
    ): array {
        $group_by = $group_by ? StatisticsGroupBy::from($group_by) : null;

        return $this->interaction_query->filterAddToCartData($shop_id, $product_id, $start_date, $end_date, $group_by);
    }
}
