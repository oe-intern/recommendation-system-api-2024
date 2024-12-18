<?php

namespace App\Http\Controllers;

use App\Contracts\Queries\IProductQuery;
use App\Contracts\Queries\IShopQuery;
use App\Contracts\Recommendation\IProductRecommendation;
use App\Exceptions\ShopNotFoundException;
use App\Services\Shopify\UserContext;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RecommendationController extends BaseController
{
    /**
     * @var IProductRecommendation
     */
    protected IProductRecommendation $product_recommendation_service;

    /**
     * RecommendationController constructor.
     *
     * @param UserContext $user_context
     * @param IProductRecommendation $product_recommendation_service
     * @param IProductQuery $product_query
     * @param IShopQuery $shop_query
     */
    public function __construct(
        UserContext $user_context,
        IProductRecommendation $product_recommendation_service,
        IProductQuery $product_query,
        IShopQuery $shop_query,
    ) {
        parent::__construct($user_context, $product_query, $shop_query);
        $this->product_recommendation_service = $product_recommendation_service;
    }

    /**
     * Get shop recommendations information.
     *
     * @throws ShopNotFoundException
     */
    public function getShopRecommendations(Request $request): Response
    {
        $shop_id = $this->getShopId();

        return response()->success('..............', '....');
    }

    /**
     * Set shop recommendations.
     *
     * @param Request $request
     * @return Response
     * @throws ShopNotFoundException
     */
    public function setShopRecommendations(Request $request): Response
    {
        $shop_id = $this->getShopId();
    }

    /**
     * Request to get state from process recommendation.
     *
     * @throws ShopNotFoundException
     */
    public function processRecommendation(Request $request): Response
    {
        $shop_id = $this->getShopId();

    }

    /**
     * Request to refresh recommendation.
     *
     * @throws ShopNotFoundException
     */
    public function refreshRecommendation(Request $request): Response
    {
        $shop_id = $this->getShopId();
    }

    /**
     * Request to cancel recommendation.
     *
     * @throws ShopNotFoundException
     */
    public function cancelRecommendation(Request $request): Response
    {
        $shop_id = $this->getShopId();
    }

}
