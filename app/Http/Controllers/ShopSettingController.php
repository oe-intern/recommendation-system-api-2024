<?php

namespace App\Http\Controllers;

use App\Contracts\Queries\IProductQuery;
use App\Contracts\Queries\IShopQuery;
use App\Contracts\Recommendation\IProductRecommendation;
use App\Exceptions\MissingProductIdException;
use App\Exceptions\ProductNotFoundException;
use App\Exceptions\ShopNotFoundException;
use App\Lib\Utils;
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
        IShopQuery $shopQuery
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
     * @param Request $request
     * @return Response
     *
     * @throws ShopNotFoundException
     */
    public function setShopSetting(Request $request): Response
    {
        $shopId = $this->getShopId();
        $settings = [
            'number_of_items' => $request->input('number_of_items'),
            'layout' => $request->input('layout'),
            'background_color' => $request->input('background_color'),
            'text_color' => $request->input('text_color')
        ];

        $settings = $this->productRecommendationService->setShopSettings($shopId, $settings);

        return response()->success('Auto recommendation settings has been set.', $settings);
    }

    /**
     * Activate recommendation for all product of a shop.
     *
     * @param Request $request
     * @return Response
     *
     * @throws ShopNotFoundException
     */
    public function activateRecommendation(Request $request): Response
    {
        $shopId = $this->getShopId();
        $status = $request->input('status');

        $this->productRecommendationService->activateRecommendation($shopId, $status);

        return response()->success('Recommendation has been ' . $status . 'd.');
    }
}
