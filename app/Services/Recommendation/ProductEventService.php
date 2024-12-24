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
    protected IProductQuery $product_query;

    /**
     * @var IEventCommand
     */
    protected IEventCommand $event_command;

    /**
     * @var IEventQuery
     */
    protected IEventQuery $event_query;

    /**
     * @var int
     */
    private int $PERFORMANCE_LIMIT = 3;

    /**
     * ProductEventService constructor.
     *
     * @param IProductQuery $product_query
     * @param IEventCommand $event_command
     * @param IEventQuery $event_query
     */
    public function __construct(
        IProductQuery $product_query,
        IEventCommand $event_command,
        IEventQuery $event_query
    ) {
        $this->product_query = $product_query;
        $this->event_command = $event_command;
        $this->event_query = $event_query;
    }

    /**
     * Handle click event.
     *
     * @param string $shop_id
     * @param string $product_id
     * @param mixed $data
     * @return void
     *
     * @throws ProductNotFoundException
     */
    public function click(string $shop_id, string $product_id, mixed $data): void
    {
        $this->product_query->validateProductId($shop_id, $product_id);

        $this->event_command->trigger($shop_id, $product_id, EventType::CLICK, $data, null);
    }

    /**
     * Handle add to cart event.
     *
     * @param string $shop_id
     * @param string $product_id
     * @param mixed $data
     * @param int|null $quantity
     * @return void
     *
     * @throws ProductNotFoundException
     */
    public function addToCart(string $shop_id, string $product_id, mixed $data, ?int $quantity): void
    {
        $this->product_query->validateProductId($shop_id, $product_id);

        $this->event_command->trigger($shop_id, $product_id, EventType::ADD_TO_CART, $data, $quantity);
    }

    /**
     * Get event analytic.
     *
     * @param string $shop_id
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    public function getProductPerformance(string $shop_id, string $start_date, string $end_date): array
    {
        $events = $this->event_query->getEventData($shop_id, $start_date, $end_date);
        $product_event_ids = array_column($events, 'id');
        $products_without_events = $this->product_query->getProductsNotIn($shop_id, $product_event_ids);

        $top_products = array_slice($events, 0, $this->PERFORMANCE_LIMIT);

        $low_products = $this->selectLowPerformingProducts(
            $product_event_ids,
            $products_without_events,
        );

        $top_products = $this->formatData($top_products);
        $low_products = $this->convertDataIdToGid($low_products);

        return [
            'top' => $top_products,
            'low' => $low_products,
        ];
    }

    /**
     * Selects low performing products from products without events.
     *
     * @param array $products_event_ids
     * @param array $products_without_events
     * @return array
     */
    private function selectLowPerformingProducts(array $products_event_ids, array $products_without_events): array
    {
        if (count($products_without_events) > $this->PERFORMANCE_LIMIT) {
            shuffle($products_without_events);

            return $this->convertToEmptyEvent(array_slice($products_without_events, 0, $this->PERFORMANCE_LIMIT));
        }

        return array_merge($this->convertToEmptyEvent($products_without_events),
            array_slice($products_event_ids, 0, count($products_without_events) - $this->PERFORMANCE_LIMIT));
    }

    /**
     * Get list of optional products for recommendation.
     *
     * @param array $product_ids
     * @return array
     */
    private function convertToEmptyEvent(array $product_ids): array
    {
        return array_map(fn($id) => ['quantity' => 0, 'id' => $id], $product_ids);
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
        return array_map(fn($product) => ['id' => $this->product_query->getGidById($product['id']), 'quantity' => $product['quantity']], $data);
    }

    /**
     * Get click data.
     *
     * @param string $shop_id
     * @param string|null $product_id
     * @param string $start_date
     * @param string $end_date
     * @param string|null $group_by
     * @return array
     */
    public function getClickData(
        string $shop_id,
        ?string $product_id,
        string $start_date,
        string $end_date,
        ?string $group_by
    ): array {
        $group_by = $group_by ? AnalyticGroupBy::from($group_by) : null;

        return $this->event_query->filterClickData($shop_id, $product_id, $start_date, $end_date, $group_by);
    }

    /**
     * Get add to cart data.
     *
     * @param string $shop_id
     * @param string|null $product_id
     * @param string $start_date
     * @param string $end_date
     * @param string|null $group_by
     * @return array
     */
    public function getAddToCartData(
        string $shop_id,
        ?string $product_id,
        string $start_date,
        string $end_date,
        ?string $group_by
    ): array {
        $group_by = $group_by ? AnalyticGroupBy::from($group_by) : null;

        return $this->event_query->filterAddToCartData($shop_id, $product_id, $start_date, $end_date, $group_by);
    }
}
