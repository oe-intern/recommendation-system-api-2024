<?php

namespace App\Storage\Queries;

use App\Collections\ProductAddToCartCollection;
use App\Collections\ProductClickCollection;
use App\Contracts\Queries\IInteractionQuery;
use App\Contracts\Queries\IShopQuery;
use App\Objects\Enums\StatisticsGroupBy;
use Carbon\Carbon;

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
        $start_date = $this->getFirstDay($start_date);
        $end_date = $this->getEndDay($end_date);

        $query = ProductClickCollection::query()
            ->where('shop_id', $shop_id)
            ->whereBetween('created_at', [$start_date, $end_date])
            ->when($product_id, function ($query) use ($product_id) {
                return $query->where('product_id', $product_id);
            });

        $result = $query->raw(function ($collection) use ($group_by, $start_date, $end_date, $shop_id) {
            return $collection->aggregate([
                [
                    '$project' => [
                        'group_key' => [
                            '$dateToString' => [
                                'format' => $this->getGroupBy($group_by),
                                'date' => '$created_at',
                            ],
                        ],
                    ],
                ],
                [
                    '$group' => [
                        '_id' => '$group_key',
                        'total' => ['$sum' => 1],
                    ],
                ],
                [
                    '$sort' => ['_id' => 1],
                ],
            ]);
        });

        return collect($result)->toArray();
    }

    /**
     * Get the first day of the date.
     *
     * @param string $date
     * @return Carbon
     */
    private function getFirstDay(string $date): Carbon
    {
        return Carbon::parse($date)->startOfDay();
    }

    private function getEndDay(string $date): Carbon
    {
        return Carbon::parse($date)->endOfDay();
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
        $start_date = $this->getFirstDay($start_date);
        $end_date = $this->getEndDay($end_date);

        $query = ProductAddToCartCollection::query()
            ->where('shop_id', $shop_id)
            ->whereBetween('created_at', [$start_date, $end_date])
            ->when($product_id, function ($query) use ($product_id) {
                return $query->where('product_id', $product_id);
            });

        $result = $query->raw(function ($collection) use ($group_by, $start_date, $end_date, $shop_id) {
            return $collection->aggregate([
                [
                    '$project' => [
                        'group_key' => [
                            '$dateToString' => [
                                'format' => $this->getGroupBy($group_by),
                                'date' => '$created_at',
                            ],
                        ],
                        'quantity' => ['$toInt' => '$quantity'],
                    ],
                ],
                [
                    '$group' => [
                        '_id' => '$group_key',
                        'quantity' => ['$sum' => '$quantity'],
                    ],
                ],
                [
                    '$sort' => ['_id' => 1],
                ],
            ]);
        });

        return collect($result)->toArray();
    }
}
