<?php

namespace App\Http\Controllers;

use App\Contracts\Queries\IProductQuery;
use App\Contracts\Queries\IShopQuery;
use App\Contracts\Recommendation\IProductRecommendation;
use App\Contracts\Recommendation\IShopRecommendation;
use App\DTO\Request\UpdateNotificationSettingsRequestDTO;
use App\Exceptions\JobRecommendationRunningException;
use App\Exceptions\RecommendationRefreshLimitException;
use App\Exceptions\ShopNotFoundException;
use App\Http\Requests\UpdateNotificationSettingsRequest;
use App\Services\Shopify\UserContext;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RecommendationController extends BaseController
{
    /**
     * @var IProductRecommendation
     */
    protected IProductRecommendation $productRecommendationService;

    /**
     * @var IShopRecommendation
     */
    protected IShopRecommendation $shopRecommendationService;

    /**
     * RecommendationController constructor.
     *
     * @param UserContext $userContext
     * @param IProductRecommendation $productRecommendationService
     * @param IShopRecommendation $shopRecommendationService
     * @param IProductQuery $productQuery
     * @param IShopQuery $shopQuery
     */
    public function __construct(
        UserContext $userContext,
        IProductRecommendation $productRecommendationService,
        IShopRecommendation $shopRecommendationService,
        IProductQuery $productQuery,
        IShopQuery $shopQuery,
    ) {
        parent::__construct($userContext, $productQuery, $shopQuery);
        $this->productRecommendationService = $productRecommendationService;
        $this->shopRecommendationService = $shopRecommendationService;
    }

    /**
     * Get shop recommendations information.
     *
     * @throws ShopNotFoundException
     */
    public function getShopRecommendations(Request $request): Response
    {
        $shopId = $this->getShopId();

        $shopRecommendations = $this->shopRecommendationService->getShopRecommendations($shopId);

        return response()->success('Shop recommendations retrieved successfully', $shopRecommendations);
    }

    /**
     * Set shop recommendations.
     *
     * @param UpdateNotificationSettingsRequest $request
     * @return Response
     * @throws ShopNotFoundException
     */
    public function setShopRecommendations(UpdateNotificationSettingsRequest $request): Response
    {
        $shopId = $this->getShopId();
        $updateNotificationSettingsRequestDTO = UpdateNotificationSettingsRequestDTO::fromRequest($request);

        $settings = $this->shopRecommendationService->updateShopRecommendationNotification(
            $shopId,
            $updateNotificationSettingsRequestDTO,
        );

        return response()->success('Shop recommendations has been set.', $settings);
    }

    /**
     * Request to get state from process recommendation.
     *
     * @throws ShopNotFoundException
     */
    public function getProcessRecommendation(Request $request): Response
    {
        $shopId = $this->getShopId();

        $processingData = $this->shopRecommendationService->getProcessingStatus($shopId);

        return response()->success('Processing recommendation retrieved successfully', $processingData);
    }

    /**
     * Request to refresh recommendation.
     *
     * @throws ShopNotFoundException|RecommendationRefreshLimitException
     * @throws JobRecommendationRunningException
     */
    public function refreshRecommendation(Request $request): Response
    {
        $shopId = $this->getShopId();
        $shopDomain = $this->userContext->getDomain()->toNative();

        $this->shopRecommendationService->refreshRecommendations($shopId, $shopDomain);

        return response()->success('Processing recommendation has been started.');
    }

    /**
     * Request to cancel recommendation.
     *
     * @throws ShopNotFoundException
     */
    public function cancelRecommendation(Request $request): Response
    {
        $shopId = $this->getShopId();
    }

}
