<?php

namespace App\Http\Controllers;

use App\Contracts\Queries\IProductQuery;
use App\Contracts\Queries\IShopQuery;
use App\Contracts\Recommendation\IProductEvent;
use App\DTO\Request\AddToCartEventRequestDTO;
use App\DTO\Request\ClickEventRequestDTO;
use App\DTO\Request\GetEventAnalyticRequestDTO;
use App\DTO\Request\GetProductPerformanceRequestDTO;
use App\Exceptions\MissingProductIdException;
use App\Exceptions\ProductNotFoundException;
use App\Exceptions\ShopNotFoundException;
use App\Http\Requests\AddToCartEventRequest;
use App\Http\Requests\ClickEventRequest;
use App\Http\Requests\GetEventAnalyticRequest;
use App\Http\Requests\GetProductPerformanceRequest;
use App\Jobs\ProcessAddToCartEvent;
use App\Jobs\ProcessClickEvent;
use App\Objects\Enums\EventType;
use App\Services\Shopify\UserContext;
use Illuminate\Http\Response;

class ProductEventController extends BaseController
{
    /**
     * @var IProductEvent
     */
    protected IProductEvent $productEventService;

    /**
     * ProductEventController constructor.
     *
     * @param UserContext $userContext
     * @param IProductEvent $productEventService
     * @param IShopQuery $shopQuery
     * @param IProductQuery $productQuery
     */
    public function __construct(
        UserContext $userContext,
        IProductEvent $productEventService,
        IShopQuery $shopQuery,
        IProductQuery $productQuery,
    ) {
        parent::__construct($userContext, $productQuery, $shopQuery);
        $this->productEventService = $productEventService;
    }

    /**
     * Get list of events for a shop.
     *
     * @param GetEventAnalyticRequest $request
     * @return Response
     *
     * @throws MissingProductIdException
     * @throws ProductNotFoundException
     * @throws ShopNotFoundException
     */
    public function getClickAnalytic(GetEventAnalyticRequest $request): Response
    {
        return $this->getStatisticsData($request, EventType::CLICK);
    }

    /**
     * Get list of events for a shop.
     *
     * @param GetEventAnalyticRequest $request
     * @param EventType $eventType
     * @return Response
     *
     * @throws MissingProductIdException
     * @throws ProductNotFoundException
     * @throws ShopNotFoundException
     */
    private function getStatisticsData(GetEventAnalyticRequest $request, EventType $eventType): Response
    {
        $shopId = $this->getShopId();
        $getEventAnalyticRequestDTO = GetEventAnalyticRequestDTO::fromRequest($request);
        $productId = $getEventAnalyticRequestDTO->productId;
        $getEventAnalyticRequestDTO->productId = $productId
            ? $this->getProductId($shopId, $productId)
            : null;

        $events = match ($eventType) {
            EventType::CLICK => $this->productEventService
                ->getClickData($shopId, $getEventAnalyticRequestDTO),
            EventType::ADD_TO_CART => $this->productEventService
                ->getAddToCartData($shopId, $getEventAnalyticRequestDTO),
        };

        return response()->success('Events retrieved successfully', $events);
    }

    /**
     * Get list of events for a shop.
     *
     * @param GetEventAnalyticRequest $request
     * @return Response
     *
     * @throws MissingProductIdException
     * @throws ProductNotFoundException
     * @throws ShopNotFoundException
     */
    public function getAddToCartAnalytic(GetEventAnalyticRequest $request): Response
    {
        return $this->getStatisticsData($request, EventType::ADD_TO_CART);
    }

    /**
     * Get info analytic for a about max, min events for a shop.
     *
     * @param GetProductPerformanceRequest $request
     * @return Response
     * @throws ShopNotFoundException
     */
    public function getProductPerformance(GetProductPerformanceRequest $request): Response
    {
        $shopId = $this->getShopId();
        $productPerformanceRequestDTO = GetProductPerformanceRequestDTO::fromRequest($request);

        $events = $this->productEventService->getProductPerformance($shopId, $productPerformanceRequestDTO);

        return response()->success('Events retrieved successfully', $events);
    }

    /**
     * Increment the number of add to cart events for a product.
     *
     * @param AddToCartEventRequest $request
     * @return Response
     *
     * @throws MissingProductIdException
     * @throws ProductNotFoundException
     * @throws ShopNotFoundException
     */
    public function addToCart(AddToCartEventRequest $request): Response
    {
        $shopId = $this->getShopId();
        $addCartEventRequestDTO = AddToCartEventRequestDTO::fromRequest($request);
        $addCartEventRequestDTO->productId = $this->getProductId($shopId, $addCartEventRequestDTO->productId);

        ProcessAddToCartEvent::dispatch($shopId, $addCartEventRequestDTO)
            ->onQueue(config('queue.queues.event-queue'));

        return response()->success('Events updated successfully');
    }

    /**
     * Increment the number of click events for a product.
     *
     * @param ClickEventRequest $request
     * @return Response
     *
     * @throws MissingProductIdException
     * @throws ProductNotFoundException
     * @throws ShopNotFoundException
     */
    public function click(ClickEventRequest $request): Response
    {
        $shopId = $this->getShopId();
        $clickEventRequestDTO = ClickEventRequestDTO::fromRequest($request);
        $clickEventRequestDTO->productId = $this->getProductId($shopId, $clickEventRequestDTO->productId);

        ProcessClickEvent::dispatch($shopId, $clickEventRequestDTO)
            ->onQueue(config('queue.queues.event-queue'));

        return response()->success('Events updated successfully');
    }
}
