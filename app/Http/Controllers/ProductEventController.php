<?php

namespace App\Http\Controllers;

use App\Contracts\Queries\IProductQuery;
use App\Contracts\Queries\IShopQuery;
use App\Contracts\Recommendation\IProductEvent;
use App\Exceptions\MissingProductIdException;
use App\Exceptions\ProductNotFoundException;
use App\Exceptions\ShopNotFoundException;
use App\Jobs\ProcessAddToCartEvent;
use App\Jobs\ProcessClickEvent;
use App\Objects\Enums\EventType;
use App\Services\Shopify\UserContext;
use Illuminate\Http\Request;
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
     * @param Request $request
     * @return Response
     *
     * @throws MissingProductIdException
     * @throws ProductNotFoundException
     * @throws ShopNotFoundException
     */
    public function getClickAnalytic(Request $request): Response
    {
        return $this->getStatisticsData($request, EventType::CLICK);
    }

    /**
     * Get list of events for a shop.
     *
     * @param Request $request
     * @return Response
     *
     * @throws MissingProductIdException
     * @throws ProductNotFoundException
     * @throws ShopNotFoundException
     */
    public function getAddToCartAnalytic(Request $request): Response
    {
        return $this->getStatisticsData($request, EventType::ADD_TO_CART);
    }

    /**
     * Get list of events for a shop.
     *
     * @param Request $request
     * @param EventType $eventType
     * @return Response
     *
     * @throws MissingProductIdException
     * @throws ProductNotFoundException
     * @throws ShopNotFoundException
     */
    private function getStatisticsData(Request $request, EventType $eventType): Response
    {
        $shopId = $this->getShopId();
        $productId = $request->query('product_id');
        $productId = $productId ? $this->getProductId($shopId, $productId) : null;
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $groupBy = $request->query('group_by');

        $events = match ($eventType) {
            EventType::CLICK => $this->productEventService
                ->getClickData($shopId, $productId, $startDate, $endDate, $groupBy),
            EventType::ADD_TO_CART => $this->productEventService
                ->getAddToCartData($shopId, $productId, $startDate, $endDate, $groupBy),
        };

        return response()->success('Events retrieved successfully', $events);
    }

    /**
     * Get info analytic for a about max, min events for a shop.
     *
     * @param Request $request
     * @return Response
     * @throws ShopNotFoundException
     */
    public function getProductPerformance(Request $request): Response
    {
        $shopId = $this->getShopId();
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $events = $this->productEventService->getProductPerformance($shopId, $startDate, $endDate);

        return response()->success('Events retrieved successfully', $events);
    }

    /**
     * Increment the number of add to cart events for a product.
     *
     * @param Request $request
     * @return Response
     *
     * @throws MissingProductIdException
     * @throws ProductNotFoundException
     * @throws ShopNotFoundException
     */
    public function addToCart(Request $request): Response
    {
        $productId = $request->input('product_id');
        $data = $request->input('data');
        $numberOfItems = $request->input('number_of_items');
        $shopId = $this->getShopId();
        $productId = $this->getProductId($shopId, $productId);

        ProcessAddToCartEvent::dispatch($shopId, $productId, $data, $numberOfItems);

        return response()->success('Events updated successfully');
    }

    /**
     * Increment the number of click events for a product.
     *
     * @param Request $request
     * @return Response
     *
     * @throws MissingProductIdException
     * @throws ProductNotFoundException
     * @throws ShopNotFoundException
     */
    public function click(Request $request): Response
    {
        $productId = $request->input('product_id');
        $data = $request->input('data');
        $shopId = $this->getShopId();
        $productId = $this->getProductId($shopId, $productId);

        ProcessClickEvent::dispatch($shopId, $productId, $data);

        return response()->success('Events updated successfully');
    }
}
