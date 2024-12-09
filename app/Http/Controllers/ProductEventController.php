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
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class ProductEventController extends BaseController
{
    /**
     * @var IProductEvent
     */
    protected IProductEvent $product_event_service;

    /**
     * ProductEventController constructor.
     *
     * @param UserContext $user_context
     * @param IProductEvent $product_event_service
     * @param IShopQuery $shop_query
     * @param IProductQuery $product_query
     */
    public function __construct(
        UserContext $user_context,
        IProductEvent $product_event_service,
        IShopQuery $shop_query,
        IProductQuery $product_query,
    ) {
        parent::__construct($user_context, $product_query, $shop_query);
        $this->product_event_service = $product_event_service;
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
     * @param EventType $event_type
     * @return Response
     *
     * @throws MissingProductIdException
     * @throws ProductNotFoundException
     * @throws ShopNotFoundException
     */
    private function getStatisticsData(Request $request, EventType $event_type): Response
    {
        $shop_id = $this->getShopId();
        $product_id = $request->query('product_id');
        $product_id = $product_id ? $this->getProductId($shop_id, $product_id) : null;
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');
        $group_by = $request->query('group_by');

        $events = match ($event_type) {
            EventType::CLICK => $this->product_event_service
                ->getClickData($shop_id, $product_id, $start_date, $end_date, $group_by),
            EventType::ADD_TO_CART => $this->product_event_service
                ->getAddToCartData($shop_id, $product_id, $start_date, $end_date, $group_by),
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
        $shop_id = $this->getShopId();
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        $events = $this->product_event_service->getProductPerformance($shop_id, $start_date, $end_date);

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
        $product_id = $request->input('product_id');
        $data = $request->input('data');
        $number_of_items = $request->input('number_of_items');
        $shop_id = $this->getShopId();
        $product_id = $this->getProductId($shop_id, $product_id);

        ProcessAddToCartEvent::dispatch($shop_id, $product_id, $data, $number_of_items);

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
        $product_id = $request->input('product_id');
        $data = $request->input('data');
        $shop_id = $this->getShopId();
        $product_id = $this->getProductId($shop_id, $product_id);

        ProcessClickEvent::dispatch($shop_id, $product_id, $data);

        return response()->success('Events updated successfully');
    }
}
