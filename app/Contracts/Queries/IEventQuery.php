<?php

namespace App\Contracts\Queries;

use App\Objects\Enums\AnalyticGroupBy;

interface IEventQuery
{
    /**
     * Filter click data for a shop
     *
     * @param string $shopId
     * @param string|null $productId
     * @param string $startDate
     * @param string $endDate
     * @param AnalyticGroupBy $groupBy
     * @return array
     */
    public function filterClickData(
        string $shopId,
        ?string $productId,
        string $startDate,
        string $endDate,
        AnalyticGroupBy $groupBy
    ): array;

    /**
     * Filter add to cart data for a shop
     *
     * @param string $shopId
     * @param string|null $productId
     * @param string $startDate
     * @param string $endDate
     * @param AnalyticGroupBy $groupBy
     * @return array
     */
    public function filterAddToCartData(
        string $shopId,
        ?string $productId,
        string $startDate,
        string $endDate,
        AnalyticGroupBy $groupBy
    ): array;

    /**
     * Get event data for a shop.
     *
     * @param string $shopId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getEventData(
        string $shopId,
        string $startDate,
        string $endDate
    ): array;
}
