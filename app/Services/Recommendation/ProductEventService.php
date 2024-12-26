<?php

namespace App\Services\Recommendation;

use App\Contracts\Commands\IEventCommand;
use App\Contracts\Queries\IEventQuery;
use App\Contracts\Queries\IProductQuery;
use App\Contracts\Recommendation\IProductEvent;
use App\DTO\Request\AddToCartEventRequestDTO;
use App\DTO\Request\ClickEventRequestDTO;
use App\DTO\Request\GetEventAnalyticRequestDTO;
use App\DTO\Request\GetProductPerformanceRequestDTO;
use App\Exceptions\ProductNotFoundException;
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
    private int $performanceLimit = 3;

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
        IEventQuery $eventQuery,
    ) {
        $this->productQuery = $productQuery;
        $this->eventCommand = $eventCommand;
        $this->eventQuery = $eventQuery;
    }

    /**
     * Handle click event.
     *
     * @param string $shopId
     * @param ClickEventRequestDTO $requestDTO
     * @return void
     *
     * @throws ProductNotFoundException
     */
    public function click(string $shopId, ClickEventRequestDTO $requestDTO): void
    {
        $this->productQuery->validateProductId($shopId, $requestDTO->productId);

        $this->eventCommand->trigger(
            $shopId,
            $requestDTO->productId,
            EventType::CLICK,
            $requestDTO->data,
            null,
        );
    }

    /**
     * Handle add to cart event.
     *
     * @param string $shopId
     * @param AddToCartEventRequestDTO $requestDTO
     * @return void
     *
     * @throws ProductNotFoundException
     */
    public function addToCart(string $shopId, AddToCartEventRequestDTO $requestDTO): void
    {
        $this->productQuery->validateProductId($shopId, $requestDTO->productId);

        $this->eventCommand->trigger(
            $shopId,
            $requestDTO->productId,
            EventType::ADD_TO_CART,
            $requestDTO->data,
            $requestDTO->numberOfItems,
        );
    }

    /**
     * Get event analytic.
     *
     * @param string $shopId
     * @param GetProductPerformanceRequestDTO $requestDTO
     * @return array
     */
    public function getProductPerformance(string $shopId, GetProductPerformanceRequestDTO $requestDTO): array
    {
        $productEventData = $this->eventQuery->getEventData(
            $shopId,
            $requestDTO->startDate,
            $requestDTO->endDate,
        );
        $productIdsWithEvents = array_column($productEventData, 'id');
        $productIdsWithoutEvents = $this->productQuery->getProductsNotIn($shopId, $productIdsWithEvents);

        $topProducts = array_slice($productEventData, 0, $this->performanceLimit);

        $lowProducts = $this->selectLowPerformingProducts(
            $productIdsWithEvents,
            $productIdsWithoutEvents,
        );

        return [
            'top' => $this->formatData($topProducts),
            'low' => $this->convertDataIdToGid($lowProducts),
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
        if (count($productIdsWithoutEvents) > $this->performanceLimit) {
            shuffle($productIdsWithoutEvents);

            return $this->convertToEmptyEvent(array_slice($productIdsWithoutEvents, 0, $this->performanceLimit));
        }

        return array_merge(
            $this->convertToEmptyEvent($productIdsWithoutEvents),
            array_slice($productIdsWithEvents, 0, count($productIdsWithoutEvents) - $this->performanceLimit),
        );
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
        return array_map(fn($product) => [
            'id' => data_get($product, 'gid'),
            'quantity' => data_get($product, 'quantity'),
        ], $data);
    }

    /**
     * Convert data id to gid.
     *
     * @param array $data
     * @return array
     */
    private function convertDataIdToGid(array $data): array
    {
        return array_map(fn($product) => [
            'id' => $this->productQuery->getGidById($product['id']),
            'quantity' => $product['quantity'],
        ], $data);
    }

    /**
     * Get click data.
     *
     * @param string $shopId
     * @param GetEventAnalyticRequestDTO $requestDTO
     * @return array
     */
    public function getClickData(
        string $shopId,
        GetEventAnalyticRequestDTO $requestDTO,
    ): array {
        return $this->eventQuery->filterClickData(
            $shopId,
            $requestDTO->productId,
            $requestDTO->startDate,
            $requestDTO->endDate,
            $requestDTO->groupBy,
        );
    }

    /**
     * Get add to cart data.
     *
     * @param string $shopId
     * @param GetEventAnalyticRequestDTO $requestDTO
     * @return array
     */
    public function getAddToCartData(
        string $shopId,
        GetEventAnalyticRequestDTO $requestDTO,
    ): array {
        return $this->eventQuery->filterAddToCartData(
            $shopId,
            $requestDTO->productId,
            $requestDTO->startDate,
            $requestDTO->endDate,
            $requestDTO->groupBy,
        );
    }
}
