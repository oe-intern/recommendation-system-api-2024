<?php

namespace App\Contracts\Queries;

use App\Objects\Enums\AnalyticGroupBy;

interface IEventQuery
{
    /**
     * Filter click data for a shop
     *
     * @param string $shop_id
     * @param string|null $product_id
     * @param string $start_date
     * @param string $end_date
     * @param AnalyticGroupBy|null $group_by
     * @return array
     */
    public function filterClickData(
        string $shop_id,
        ?string $product_id,
        string $start_date,
        string $end_date,
        ?AnalyticGroupBy $group_by
    ): array;

    /**
     * Filter add to cart data for a shop
     *
     * @param string $shop_id
     * @param string|null $product_id
     * @param string $start_date
     * @param string $end_date
     * @param AnalyticGroupBy|null $group_by
     * @return array
     */
    public function filterAddToCartData(
        string $shop_id,
        ?string $product_id,
        string $start_date,
        string $end_date,
        ?AnalyticGroupBy $group_by
    ): array;

    /**
     * Get event data for a shop.
     *
     * @param string $shop_id
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    public function getEventData(
        string $shop_id,
        string $start_date,
        string $end_date
    ): array;
}
