<?php

namespace App\Storage\Queries;

use App\Collections\EventCollection;
use App\Contracts\Queries\IEventQuery;
use App\Contracts\Queries\IShopQuery;
use App\Objects\Enums\AnalyticGroupBy;
use App\Objects\Enums\EventType;
use Carbon\Carbon;
use MongoDB\BSON\UTCDateTime;

class EventQuery implements IEventQuery
{
    /**
     * @var IShopQuery
     */
    protected IShopQuery $shop_query;

    /**
     * EventQuery constructor.
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
     * @param AnalyticGroupBy|null $group_by
     * @return array
     */
    public function filterClickData(
        string $shop_id,
        ?string $product_id,
        string $start_date,
        string $end_date,
        ?AnalyticGroupBy $group_by
    ): array {
        return $this->filterEventData(
            EventType::CLICK,
            $shop_id,
            $product_id,
            $start_date,
            $end_date,
            $group_by
        );
    }

    /**
     * Get query for event data
     *
     * @param EventType $type
     * @param string $shop_id
     * @param string|null $product_id
     * @param string $start_date
     * @param string $end_date
     * @param AnalyticGroupBy|null $group_by
     * @return array
     */
    private function filterEventData(
        EventType $type,
        string $shop_id,
        ?string $product_id,
        string $start_date,
        string $end_date,
        ?AnalyticGroupBy $group_by
    ): array {
        $start_date = $this->getFirstDay($start_date);
        $end_date = $this->getEndDay($end_date);

        $result = EventCollection::query()
            ->raw(function ($collection) use (
                $group_by,
                $shop_id,
                $product_id,
                $type,
                $start_date,
                $end_date
            ) {
                return $collection->aggregate([
                    [
                        '$match' => [
                            'shop_id' => $shop_id,
                            'product_id' => $product_id ?? ['$exists' => true],
                            'created_at' => [
                                '$gte' => $start_date,
                                '$lte' => $end_date,
                            ],
                            'type' => $type->value,
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
                            'quantity' => ['$toInt' => '$quantity'],
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

    /**
     * @param string $date
     * @return UTCDateTime
     */
    private function getEndDay(string $date): UTCDateTime
    {
        $end_day = Carbon::parse($date)->endOfDay();
        return new UTCDateTime($end_day);
    }

    /**
     * Get the group by format for the query.
     *
     * @param AnalyticGroupBy|null $groupBy
     * @return string
     */
    private function getGroupBy(?AnalyticGroupBy $groupBy): string
    {
        return match ($groupBy) {
            AnalyticGroupBy::HOUR => '%Y-%m-%d %H',
            AnalyticGroupBy::MONTH => '%Y-%m',
            AnalyticGroupBy::YEAR => '%Y',
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
     * @param AnalyticGroupBy|null $group_by
     * @return array
     */
    public function filterAddToCartData(
        string $shop_id,
        ?string $product_id,
        string $start_date,
        string $end_date,
        ?AnalyticGroupBy $group_by
    ): array {
        return $this->filterEventData(
            EventType::ADD_TO_CART,
            $shop_id,
            $product_id,
            $start_date,
            $end_date,
            $group_by
        );
    }

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
    ): array {
        $start_date = $this->getFirstDay($start_date);
        $end_date = $this->getEndDay($end_date);

        $result = EventCollection::query()
            ->raw(function ($collection) use ($shop_id, $start_date, $end_date) {
                return $collection->aggregate([
                    [
                        '$match' => [
                            'shop_id' => $shop_id,
                            'created_at' => [
                                '$gte' => $start_date,
                                '$lte' => $end_date,
                            ],
                        ],
                    ],
                    [
                        '$group' => [
                            '_id' => '$product_id',
                            'quantity' => ['$sum' => '$quantity'],
                        ],
                    ],
                    [
                        '$sort' => ['quantity' => -1],
                    ],
                ]);
            });

        return collect($result)->toArray();
    }
}
