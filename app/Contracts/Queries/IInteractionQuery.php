<?php

namespace App\Contracts\Queries;

use App\Objects\Enums\StatisticsGroupBy;

interface IInteractionQuery
{
    /**
     * Filter click data for a shop
     *
     * @param string $shop_id
     * @param string|null $product_id
     * @param string $start_date
     * @param string $end_date
     * @param StatisticsGroupBy|null $group_by
     * @return array
     */
    public function filterClickData(
        string $shop_id,
        ?string $product_id,
        string $start_date,
        string $end_date,
        ?StatisticsGroupBy $group_by
    ): array;

    /**
     * Filter add to cart data for a shop
     *
     * @param string $shop_id
     * @param string|null $product_id
     * @param string $start_date
     * @param string $end_date
     * @param StatisticsGroupBy|null $group_by
     * @return array
     */
    public function filterAddToCartData(
        string $shop_id,
        ?string $product_id,
        string $start_date,
        string $end_date,
        ?StatisticsGroupBy $group_by
    ): array;
}
