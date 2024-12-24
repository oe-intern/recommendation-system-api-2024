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
    protected IShopQuery $shopQuery;

    /**
     * EventQuery constructor.
     *
     * @param IShopQuery $shopQuery
     */
    public function __construct(IShopQuery $shopQuery)
    {
        $this->shopQuery = $shopQuery;
    }

    /**
     * Filter click data for a shop
     *
     * @param string $shopId
     * @param string|null $productId
     * @param string $startDate
     * @param string $endDate
     * @param AnalyticGroupBy|null $groupBy
     * @return array
     */
    public function filterClickData(
        string $shopId,
        ?string $productId,
        string $startDate,
        string $endDate,
        ?AnalyticGroupBy $groupBy
    ): array {
        return $this->filterEventData(
            EventType::CLICK,
            $shopId,
            $productId,
            $startDate,
            $endDate,
            $groupBy
        );
    }

    /**
     * Get query for event data
     *
     * @param EventType $type
     * @param string $shopId
     * @param string|null $productId
     * @param string $startDate
     * @param string $endDate
     * @param AnalyticGroupBy|null $groupBy
     * @return array
     */
    private function filterEventData(
        EventType $type,
        string $shopId,
        ?string $productId,
        string $startDate,
        string $endDate,
        ?AnalyticGroupBy $groupBy
    ): array {
        $startDate = $this->getFirstDay($startDate);
        $endDate = $this->getEndDay($endDate);

        $result = EventCollection::query()
            ->raw(function ($collection) use (
                $groupBy,
                $shopId,
                $productId,
                $type,
                $startDate,
                $endDate
            ) {
                return $collection->aggregate([
                    [
                        '$match' => [
                            'shop_id' => $shopId,
                            'product_id' => $productId ?? ['$exists' => true],
                            'created_at' => [
                                '$gte' => $startDate,
                                '$lte' => $endDate,
                            ],
                            'type' => $type->value,
                        ],
                    ],
                    [
                        '$project' => [
                            'group_key' => [
                                '$dateToString' => [
                                    'format' => $this->getGroupBy($groupBy),
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
                        '$lookup' => [
                            'from' => 'products',
                            'localField' => 'product_id',
                            'foreignField' => 'id',
                            'as' => 'product',
                        ],
                    ],
                    [
                        '$match' => [
                            'product' => ['$ne' => []],
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
        $startDay = Carbon::parse($date)->startOfDay();
        return new UTCDateTime($startDay);
    }

    /**
     * @param string $date
     * @return UTCDateTime
     */
    private function getEndDay(string $date): UTCDateTime
    {
        $endDay = Carbon::parse($date)->endOfDay();
        return new UTCDateTime($endDay);
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
     * @param string $shopId
     * @param string|null $productId
     * @param string $startDate
     * @param string $endDate
     * @param AnalyticGroupBy|null $groupBy
     * @return array
     */
    public function filterAddToCartData(
        string $shopId,
        ?string $productId,
        string $startDate,
        string $endDate,
        ?AnalyticGroupBy $groupBy
    ): array {
        return $this->filterEventData(
            EventType::ADD_TO_CART,
            $shopId,
            $productId,
            $startDate,
            $endDate,
            $groupBy
        );
    }

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
    ): array {
        $startDate = $this->getFirstDay($startDate);
        $endDate = $this->getEndDay($endDate);

        $result = EventCollection::query()
            ->raw(function ($collection) use ($shopId, $startDate, $endDate) {
                return $collection->aggregate([
                    [
                        '$match' => [
                            'shop_id' => $shopId,
                            'created_at' => [
                                '$gte' => $startDate,
                                '$lte' => $endDate,
                            ],
                        ],
                    ],
                    [
                        '$group' => [
                            '_id' => '$productId',
                            'quantity' => ['$sum' => '$quantity'],
                        ],
                    ],
                    [
                        '$addFields' => [
                            'product_id' => ['$toObjectId' => '$_id'],
                        ],
                    ],
                    [
                        '$lookup' => [
                            'from' => 'products',
                            'localField' => 'product_id',
                            'foreignField' => '_id',
                            'as' => 'product',
                        ],
                    ],
                    [
                        '$match' => [
                            'product' => ['$ne' => []],
                        ],
                    ],
                    [
                        '$sort' => ['quantity' => -1],
                    ],
                    [
                        '$project' => [
                            'id' => '$_id',
                            'gid' => ['$arrayElemAt' => ['$product.gid', 0]],
                            'quantity' => 1,
                            '_id' => 0,
                        ],
                    ]
                ]);
            });

        return collect($result)->toArray();
    }
}
