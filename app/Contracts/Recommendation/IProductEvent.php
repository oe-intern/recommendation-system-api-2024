<?php

namespace App\Contracts\Recommendation;

use App\Exceptions\ProductNotFoundException;

interface IProductEvent
{
    /**
     * Handle click event.
     *
     * @param string $shopId
     * @param string $productId
     * @param mixed $data
     * @return void
     *
     * @throws ProductNotFoundException
     */
    public function click(string $shopId, string $productId, mixed $data): void;

    /**
     * Handle add to cart event.
     *
     * @param string $shopId
     * @param string $productId
     * @param mixed $data
     * @param int|null $quantity
     * @return void
     *
     * @throws ProductNotFoundException
     */
    public function addToCart(string $shopId, string $productId, mixed $data, ?int $quantity): void;

    /**
     * Get event analytic.
     *
     * @param string $shopId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getProductPerformance(string $shopId, string $startDate, string $endDate): array;

    /**
     * Get click data for a product.
     *
     * @param string $shopId
     * @param string|null $productId
     * @param string $startDate
     * @param string $endDate
     * @param string|null $groupBy
     * @return array
     *
     * @throws ProductNotFoundException
     */
    public function getClickData(
        string $shopId,
        ?string $productId,
        string $startDate,
        string $endDate,
        ?string $groupBy
    ): array;

    /**
     * Get add to cart data for a product.
     *
     * @param string $shopId
     * @param string|null $productId
     * @param string $startDate
     * @param string $endDate
     * @param string|null $groupBy
     * @return array
     *
     * @throws ProductNotFoundException
     */
    public function getAddToCartData(
        string $shopId,
        ?string $productId,
        string $startDate,
        string $endDate,
        ?string $groupBy
    ): array;


}
