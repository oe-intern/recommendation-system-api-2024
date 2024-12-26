<?php

namespace App\Http\Controllers;

use App\Contracts\Queries\IProductQuery;
use App\Contracts\Queries\IShopQuery;
use App\Contracts\Recommendation\IProductRecommendation;
use App\DTO\Request\SetActiveRecommendationRequestDTO;
use App\DTO\Request\UpdateShopSettingRequestDTO;
use App\Exceptions\ShopNotFoundException;
use App\Http\Requests\SetActiveRecommendationRequest;
use App\Http\Requests\UpdateShopSettingRequest;
use App\Services\Shopify\UserContext;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ShopSettingController extends BaseController
{
    /**
     * @var IProductRecommendation
     */
    protected IProductRecommendation $productRecommendationService;

    /**
     * RecommendationController constructor.
     *
     * @param UserContext $userContext
     * @param IProductRecommendation $productRecommendationService
     * @param IProductQuery $productQuery
     * @param IShopQuery $shopQuery
     */
    public function __construct(
        UserContext $userContext,
        IProductRecommendation $productRecommendationService,
        IProductQuery $productQuery,
        IShopQuery $shopQuery,
    ) {
        parent::__construct($userContext, $productQuery, $shopQuery);
        $this->productRecommendationService = $productRecommendationService;
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @throws ShopNotFoundException
     */
    public function getShopSetting(Request $request): Response
    {
        $shopId = $this->getShopId();

        $settings = $this->productRecommendationService->getShopSettings($shopId);

        return response()->success('Auto recommendation settings retrieved successfully', $settings);
    }

    /**
     * Set auto recommendation settings for a shop.
     *
     * @param UpdateShopSettingRequest $request
     * @return Response
     *
     * @throws ShopNotFoundException
     */
    public function setShopSetting(UpdateShopSettingRequest $request): Response
    {
        $shopId = $this->getShopId();
        $updateShopSettingRequestDTO = UpdateShopSettingRequestDTO::fromRequest($request);

        $settings = $this->productRecommendationService->setShopSettings($shopId, $updateShopSettingRequestDTO);

        return response()->success('Auto recommendation settings has been set.', $settings);
    }

    /**
     * Activate recommendation for all product of a shop.
     *
     * @param SetActiveRecommendationRequest $request
     * @return Response
     *
     * @throws ShopNotFoundException
     */
    public function activateRecommendation(SetActiveRecommendationRequest $request): Response
    {
        $shopId = $this->getShopId();
        $setActiveRecommendationRequestDTO = SetActiveRecommendationRequestDTO::fromRequest($request);

        $this->productRecommendationService->activateRecommendation($shopId, $setActiveRecommendationRequestDTO);

        return response()->success('Recommendation has been ' . $setActiveRecommendationRequestDTO->status->value . 'D.');
    }
}
