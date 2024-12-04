<?php

namespace App\Storage\Queries;

use App\Collections\ProductAddToCartCollection;
use App\Collections\ProductClickCollection;
use App\Contracts\Queries\IInteractionQuery;
use App\Contracts\Queries\IShopQuery;
use App\Objects\Enums\StatisticsGroupBy;
use Carbon\Carbon;
use App\Objects\Enums\InteractionType;
use MongoDB\BSON\UTCDateTime;

class InteractionProductQuery implements IInteractionQuery
{
    /**
     * @var IShopQuery
     */
    protected IShopQuery $shop_query;

    /**
     * InteractionProductQuery constructor.
     *
     * @param IShopQuery $shop_query
     */
    public function __construct(IShopQuery $shop_query)
    {
        $this->shop_query = $shop_query;
    }

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
    ): array {
        return $this->filterInteractionData(
            InteractionType::CLICK,
            $shop_id,
            $product_id,
            $start_date,
            $end_date,
            $group_by
        );
    }

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
    ): array {
        return $this->filterInteractionData(
            InteractionType::ADD_TO_CART,
            $shop_id,
            $product_id,
            $start_date,
            $end_date,
            $group_by
        );
    }

    /**
     * Get the first day of the date.
     *
     * @param string $date
     * @return UTCDateTime
     */
    private function getFirstDay(string $date): UTCDateTime
    {
        $start_day = Carbon::parse($date)->startOfDay();
        return new UTCDateTime($start_day);
    }

    private function getEndDay(string $date): UTCDateTime
    {
        $end_day = Carbon::parse($date)->endOfDay();
        return new UTCDateTime($end_day);
    }

    /**
     * Get the group by format for the query.
     *
     * @param StatisticsGroupBy|null $groupBy
     * @return string
     */
    private function getGroupBy(?StatisticsGroupBy $groupBy): string
    {
        return match ($groupBy) {
            StatisticsGroupBy::HOUR => '%Y-%m-%d %H',
            StatisticsGroupBy::MONTH => '%Y-%m',
            StatisticsGroupBy::YEAR => '%Y',
            default => '%Y-%m-%d',
        };
    }

    /**
     * Get query for interaction data
     *
     * @param InteractionType $type
     * @param string $shop_id
     * @param string|null $product_id
     * @param string $start_date
     * @param string $end_date
     * @param StatisticsGroupBy|null $group_by
     * @return array
     */
    private function filterInteractionData(
        InteractionType $type,
        string $shop_id,
        ?string $product_id,
        string $start_date,
        string $end_date,
        ?StatisticsGroupBy $group_by
    ): array {
        $start_date = $this->getFirstDay($start_date);
        $end_date = $this->getEndDay($end_date);

        $query = match ($type) {
            InteractionType::CLICK => ProductClickCollection::query(),
            InteractionType::ADD_TO_CART => ProductAddToCartCollection::query(),
        };

        $result = $query->raw(function ($collection) use ($group_by, $shop_id, $product_id, $type, $start_date, $end_date) {
            return $collection->aggregate([
                [
                    '$match' => [
                        'shop_id' => $shop_id,
                        'product_id' => $product_id ?? ['$exists' => true],
                        'created_at' => [
                            '$gte' => $start_date,
                            '$lte' => $end_date,
                        ],
                    ],
                ],
                [
                    '$project' => [
                        'group_key' => [
                            '$dateToString' => [
                                'format' => $this->getGroupBy($group_by),
                                'date' => '$created_at',
                            ],
                        ],
                        'quantity' => match ($type) {
                            InteractionType::CLICK => ['$literal' => 1],
                            InteractionType::ADD_TO_CART => ['$toInt' => '$quantity'],
                        },
                    ],
                ],
                [
                    '$group' => [
                        '_id' => '$group_key',
                        'date' => ['$first' => '$group_key'],
                        'quantity' => ['$sum' => '$quantity'],
                    ],
                ],
                [
                    '$project' => [
                        '_id' => 0,
                        'date' => 1,
                        'quantity' => 1,
                    ],
                ],
                [
                    '$sort' => ['date' => 1],
                ],
            ]);
        });

        return collect($result)->toArray();
    }
}
