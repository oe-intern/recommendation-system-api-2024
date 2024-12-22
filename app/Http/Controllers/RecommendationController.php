<?php

namespace App\Http\Controllers;

use App\Contracts\Queries\IProductQuery;
use App\Contracts\Queries\IShopQuery;
use App\Contracts\Recommendation\IProductRecommendation;
use App\Contracts\Recommendation\IShopRecommendation;
use App\Exceptions\JobRecommendationRunningException;
use App\Exceptions\RecommendationRefreshLimitException;
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
     * @var IShopRecommendation
     */
    protected IShopRecommendation $shop_recommendation_service;

    /**
     * RecommendationController constructor.
     *
     * @param UserContext $user_context
     * @param IProductRecommendation $product_recommendation_service
     * @param IShopRecommendation $shop_recommendation_service
     * @param IProductQuery $product_query
     * @param IShopQuery $shop_query
     */
    public function __construct(
        UserContext $user_context,
        IProductRecommendation $product_recommendation_service,
        IShopRecommendation $shop_recommendation_service,
        IProductQuery $product_query,
        IShopQuery $shop_query,
    ) {
        parent::__construct($user_context, $product_query, $shop_query);
        $this->product_recommendation_service = $product_recommendation_service;
        $this->shop_recommendation_service = $shop_recommendation_service;
    }

    /**
     * Get shop recommendations information.
     *
     * @throws ShopNotFoundException
     */
    public function getShopRecommendations(Request $request): Response
    {
        $shop_id = $this->getShopId();

        $shop_recommendations = $this->shop_recommendation_service->getShopRecommendations($shop_id);

        return response()->success('Shop recommendations retrieved successfully', $shop_recommendations);
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
        $settings = [
            'email' => $request->input('email'),
            'email_notification' => $request->input('email_notification'),
        ];

        $settings = $this->shop_recommendation_service->updateShopRecommendationNotification($shop_id, $settings);

        return response()->success('Shop recommendations has been set.', $settings);
    }

    /**
     * Request to get state from process recommendation.
     *
     * @throws ShopNotFoundException
     */
    public function getProcessRecommendation(Request $request): Response
    {
        $shop_id = $this->getShopId();

        $processing_data = $this->shop_recommendation_service->getProcessingStatus($shop_id);

        return response()->success('Processing recommendation retrieved successfully', $processing_data);
    }

    /**
     * Request to refresh recommendation.
     *
     * @throws ShopNotFoundException|RecommendationRefreshLimitException
     * @throws JobRecommendationRunningException
     */
    public function refreshRecommendation(Request $request): Response
    {
        $shop_id = $this->getShopId();
        $shop_domain = $this->user_context->getDomain()->toNative();

        $this->shop_recommendation_service->refreshRecommendations($shop_id, $shop_domain);

        return response()->success('Processing recommendation has been started.');
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
