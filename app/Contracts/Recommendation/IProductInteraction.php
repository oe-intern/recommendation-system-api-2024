<?php

namespace App\Contracts\Recommendation;

use App\Exceptions\ProductNotFoundException;

interface IProductInteraction
{
    /**
     * Handle click event.
     *
     * @param string $shop_id
     * @param string $product_id
     * @return void
     *
     * @throws ProductNotFoundException
     */
    public function click(string $shop_id, string $product_id): void;

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
    public function addToCart(string $shop_id, string $product_id, ?int $quantity): void;

    /**
     * Get interaction statistics.
     *
     * @param string $shop_id
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    public function getInteractionStatistics(string $shop_id, string $start_date, string $end_date): array;

    /**
     * Get click data for a product.
     *
     * @param string $shop_id
     * @param string|null $product_id
     * @param string $start_date
     * @param string $end_date
     * @param string|null $group_by
     * @return array
     *
     * @throws ProductNotFoundException
     */
    public function getClickData(
        string $shop_id,
        ?string $product_id,
        string $start_date,
        string $end_date,
        ?string $group_by
    ): array;

    /**
     * Get add to cart data for a product.
     *
     * @param string $shop_id
     * @param string|null $product_id
     * @param string $start_date
     * @param string $end_date
     * @param string|null $group_by
     * @return array
     *
     * @throws ProductNotFoundException
     */
    public function getAddToCartData(
        string $shop_id,
        ?string $product_id,
        string $start_date,
        string $end_date,
        ?string $group_by
    ): array;


}
