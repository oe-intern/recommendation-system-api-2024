<?php

namespace App\Services\Product;

use App\Contracts\Commands\IInteractionProductCommand;
use App\Contracts\Queries\IInteractionQuery;
use App\Contracts\Queries\IProductQuery;
use App\Contracts\Recommendation\IProductInteraction;
use App\Exceptions\ProductNotFoundException;
use App\Objects\Enums\InteractionType;

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
     * Increment the number of interactions for a product.
     *
     * @param string $shop_domain
     * @param string $product_id
     * @param int|null $quantity
     * @param string $interaction_type
     * @return void
     *
     * @throws ProductNotFoundException
     */
    public function increment(string $shop_domain, string $product_id, ?int $quantity, string $interaction_type): void
    {
        $this->product_query->validateProductId($shop_domain, $product_id);

        $interaction_type = InteractionType::from($interaction_type);
        $this->interaction_product_command->increment($shop_domain, $product_id, $interaction_type, $quantity ?? 1);
    }

    /**
     * Filter list of interactions for a shop.
     *
     * @param string $shop_domain
     * @param string|null $product_id
     * @param string $start_date
     * @param string $end_date
     * @return array
     *
     * @throws ProductNotFoundException
     */
    public function filter(string $shop_domain, ?string $product_id, string $start_date, string $end_date): array
    {
        $product = null;
        if ($product_id) {
            $product = $this->product_query->validateProductId($shop_domain, $product_id);
        }
        return $this->interaction_query->filter($shop_domain, $product, $start_date, $end_date);
    }
}
