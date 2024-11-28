<?php

namespace App\Contracts\Recommendation;

use App\Exceptions\ProductNotFoundException;

interface IProductInteraction
{
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
    public function increment(string $shop_domain, string $product_id, ?int $quantity, string $interaction_type): void;

    /**
     * Filter the number of interactions for a product.
     *
     * @param string $shop_domain
     * @param string|null $product_id
     * @param string $start_date
     * @param string $end_date
     * @return array
     *
     * @throws ProductNotFoundException
     */
    public function filter(string $shop_domain, ?string $product_id, string $start_date, string $end_date): array;
}
