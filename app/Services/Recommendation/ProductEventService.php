<?php

namespace App\Services\Recommendation;

use App\Contracts\Commands\IEventCommand;
use App\Contracts\Queries\IEventQuery;
use App\Contracts\Queries\IProductQuery;
use App\Contracts\Recommendation\IProductEvent;
use App\Exceptions\ProductNotFoundException;
use App\Objects\Enums\AnalyticGroupBy;
use App\Objects\Enums\EventType;

class ProductEventService implements IProductEvent
{
    /**
     * @var IProductQuery
     */
    protected IProductQuery $productQuery;

    /**
     * @var IEventCommand
     */
    protected IEventCommand $eventCommand;

    /**
     * @var IEventQuery
     */
    protected IEventQuery $eventQuery;

    /**
     * @var int
     */
    private int $PERFORMANCE_LIMIT = 3;

    /**
     * ProductEventService constructor.
     *
     * @param IProductQuery $productQuery
     * @param IEventCommand $eventCommand
     * @param IEventQuery $eventQuery
     */
    public function __construct(
        IProductQuery $productQuery,
        IEventCommand $eventCommand,
        IEventQuery $eventQuery
    ) {
        $this->productQuery = $productQuery;
        $this->eventCommand = $eventCommand;
        $this->eventQuery = $eventQuery;
    }

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
    public function click(string $shopId, string $productId, mixed $data): void
    {
        $this->productQuery->validateProductId($shopId, $productId);

        $this->eventCommand->trigger($shopId, $productId, EventType::CLICK, $data, null);
    }

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
    public function addToCart(string $shopId, string $productId, mixed $data, ?int $quantity): void
    {
        $this->productQuery->validateProductId($shopId, $productId);

        $this->eventCommand->trigger($shopId, $productId, EventType::ADD_TO_CART, $data, $quantity);
    }

    /**
     * Get event analytic.
     *
     * @param string $shopId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getProductPerformance(string $shopId, string $startDate, string $endDate): array
    {
        $productEventData = $this->eventQuery->getEventData($shopId, $startDate, $endDate);
        $productIdsWithEvents = array_column($productEventData, 'id');
        $productIdsWithoutEvents = $this->productQuery->getProductsNotIn($shopId, $productIdsWithEvents);

        $topProducts = array_slice($productEventData, 0, $this->PERFORMANCE_LIMIT);

        $lowProducts    = $this->selectLowPerformingProducts(
            $productIdsWithEvents,
            $productIdsWithoutEvents,
        );

        $topProducts = $this->formatData($topProducts);
        $lowProducts = $this->convertDataIdToGid($lowProducts);

        return [
            'top' => $topProducts,
            'low' => $lowProducts,
        ];
    }

    /**
     * Selects low performing products from products without events.
     *
     * @param array $productIdsWithEvents
     * @param array $productIdsWithoutEvents
     * @return array
     */
    private function selectLowPerformingProducts(array $productIdsWithEvents, array $productIdsWithoutEvents): array
    {
        if (count($productIdsWithoutEvents) > $this->PERFORMANCE_LIMIT) {
            shuffle($productIdsWithoutEvents);

            return $this->convertToEmptyEvent(array_slice($productIdsWithoutEvents, 0, $this->PERFORMANCE_LIMIT));
        }

        return array_merge($this->convertToEmptyEvent($productIdsWithoutEvents),
            array_slice($productIdsWithEvents, 0, count($productIdsWithoutEvents) - $this->PERFORMANCE_LIMIT));
    }

    /**
     * Get list of optional products for recommendation.
     *
     * @param array $productIds
     * @return array
     */
    private function convertToEmptyEvent(array $productIds): array
    {
        return array_map(fn($id) => ['quantity' => 0, 'id' => $id], $productIds);
    }

    /**
     * Format data response.
     *
     * @param array $data
     * @return array
     */
    private function formatData(array $data): array
    {
        return array_map(fn($product) => ['id' => data_get($product, 'gid'), 'quantity' => data_get($product, 'quantity')], $data);
    }

    /**
     * Convert data id to gid.
     *
     * @param array $data
     * @return array
     */
    private function convertDataIdToGid(array $data): array
    {
        return array_map(fn($product) => ['id' => $this->productQuery->getGidById($product['id']), 'quantity' => $product['quantity']], $data);
    }

    /**
     * Get click data.
     *
     * @param string $shopId
     * @param string|null $productId
     * @param string $startDate
     * @param string $endDate
     * @param string|null $groupBy
     * @return array
     */
    public function getClickData(
        string $shopId,
        ?string $productId,
        string $startDate,
        string $endDate,
        ?string $groupBy
    ): array {
        $groupBy = $groupBy ? AnalyticGroupBy::from($groupBy) : null;

        return $this->eventQuery->filterClickData($shopId, $productId, $startDate, $endDate, $groupBy);
    }

    /**
     * Get add to cart data.
     *
     * @param string $shopId
     * @param string|null $productId
     * @param string $startDate
     * @param string $endDate
     * @param string|null $groupBy
     * @return array
     */
    public function getAddToCartData(
        string $shopId,
        ?string $productId,
        string $startDate,
        string $endDate,
        ?string $groupBy
    ): array {
        $groupBy = $groupBy ? AnalyticGroupBy::from($groupBy) : null;

        return $this->eventQuery->filterAddToCartData($shopId, $productId, $startDate, $endDate, $groupBy);
    }
}
