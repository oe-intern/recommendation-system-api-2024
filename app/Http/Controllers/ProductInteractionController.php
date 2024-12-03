<?php

namespace App\Http\Controllers;

use App\Contracts\Queries\IProductQuery;
use App\Contracts\Queries\IShopQuery;
use App\Contracts\Recommendation\IProductInteraction;
use App\Exceptions\MissingProductIdException;
use App\Exceptions\ProductNotFoundException;
use App\Exceptions\ShopNotFoundException;
use App\Jobs\ProcessAddToCartEvent;
use App\Jobs\ProcessClickEvent;
use App\Objects\Enums\InteractionType;
use App\Services\Shopify\UserContext;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductInteractionController extends BaseController
{
    /**
     * @var IProductInteraction
     */
    protected IProductInteraction $product_interaction_service;

    /**
     * ProductInteractionController constructor.
     *
     * @param UserContext $user_context
     * @param IProductInteraction $product_interaction_service
     * @param IShopQuery $shop_query
     * @param IProductQuery $product_query
     */
    public function __construct(
        UserContext $user_context,
        IProductInteraction $product_interaction_service,
        IShopQuery $shop_query,
        IProductQuery $product_query,
    ) {
        parent::__construct($user_context, $product_query, $shop_query);
        $this->product_interaction_service = $product_interaction_service;
    }

    /**
     * Get list of interactions for a shop.
     *
     * @param Request $request
     * @return Response
     *
     * @throws MissingProductIdException
     * @throws ProductNotFoundException
     * @throws ShopNotFoundException
     */
    public function getClickStatistics(Request $request): Response
    {
        return $this->getStatisticsData($request, InteractionType::CLICK);
    }

    /**
     * Get list of interactions for a shop.
     *
     * @param Request $request
     * @return Response
     *
     * @throws MissingProductIdException
     * @throws ProductNotFoundException
     * @throws ShopNotFoundException
     */
    public function getAddToCartStatistics(Request $request): Response
    {
        return $this->getStatisticsData($request, InteractionType::ADD_TO_CART);
    }

    /**
     * Get list of interactions for a shop.
     *
     * @param Request $request
     * @param InteractionType $interaction_type
     * @return Response
     *
     * @throws MissingProductIdException
     * @throws ProductNotFoundException
     * @throws ShopNotFoundException
     */
    private function getStatisticsData(Request $request, InteractionType $interaction_type): Response
    {
        $shop_id = $this->getShopId();
        $product_id = $request->query('product_id');
        $product_id = $product_id ? $this->getProductId($shop_id, $product_id) : null;
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');
        $group_by = $request->query('group_by');

        $interactions = match ($interaction_type) {
            InteractionType::CLICK => $this->product_interaction_service
                ->getClickData($shop_id, $product_id, $start_date, $end_date, $group_by),
            InteractionType::ADD_TO_CART => $this->product_interaction_service
                ->getAddToCartData($shop_id, $product_id, $start_date, $end_date, $group_by),
        };

        return response()->success('Interactions retrieved successfully', $interactions);
    }

    /**
     * Increment the number of add to cart interactions for a product.
     *
     * @param string $product_id
     * @param Request $request
     * @return Response
     *
     * @throws MissingProductIdException
     * @throws ProductNotFoundException
     * @throws ShopNotFoundException
     */
    public function addToCart(string $product_id, Request $request): Response
    {
        $shop_id = $this->getShopId();
        $product_id = $this->getProductId($shop_id, $product_id);
        $data = $request->all();

        ProcessAddToCartEvent::dispatch($shop_id, $product_id, $data);

        return response()->success('Interactions updated successfully');
    }

    /**
     * Increment the number of click interactions for a product.
     *
     * @param string $product_id
     * @param Request $request
     * @return Response
     *
     * @throws MissingProductIdException
     * @throws ProductNotFoundException
     * @throws ShopNotFoundException
     */
    public function click(string $product_id, Request $request): Response
    {
        $shop_id = $this->getShopId();
        $product_id = $this->getProductId($shop_id, $product_id);
        $data = $request->all();

        ProcessClickEvent::dispatch($shop_id, $product_id, $data);

        return response()->success('Interactions updated successfully');
    }
}
