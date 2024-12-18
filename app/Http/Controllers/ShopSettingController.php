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
        IShopQuery $shop_query
    ) {
        parent::__construct($user_context, $product_query, $shop_query);
        $this->product_recommendation_service = $product_recommendation_service;
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @throws ShopNotFoundException
     */
    public function getShopSetting(Request $request): Response
    {
        $shop_id = $this->getShopId();

        $settings = $this->product_recommendation_service->getShopSettings($shop_id);

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
        $shop_id = $this->getShopId();
        $settings = [
            'number_of_items' => $request->input('number_of_items'),
            'layout' => $request->input('layout'),
            'background_color' => $request->input('background_color'),
            'text_color' => $request->input('text_color')
        ];

        $settings = $this->product_recommendation_service->setShopSettings($shop_id, $settings);

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
        $shop_id = $this->getShopId();
        $status = $request->input('status');

        $this->product_recommendation_service->activateRecommendation($shop_id, $status);

        return response()->success('Recommendation has been ' . $status . 'd.');
    }
}
