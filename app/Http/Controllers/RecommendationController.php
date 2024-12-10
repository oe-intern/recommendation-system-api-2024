<?php

namespace App\Http\Controllers;

use App\Contracts\Queries\IProductQuery;
use App\Contracts\Recommendation\IProductRecommendation;
use App\Exceptions\ProductNotFoundException;
use App\Contracts\Queries\IShopQuery;
use App\Exceptions\ShopNotFoundException;
use App\Exceptions\MissingProductIdException;
use Illuminate\Http\Response;
use App\Lib\Utils;
use App\Services\Shopify\UserContext;
use Illuminate\Http\Request;

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
        IShopQuery $shop_query
    ) {
        parent::__construct($user_context, $product_query, $shop_query);
        $this->product_recommendation_service = $product_recommendation_service;
    }

    /**
     * Get list of recommendations for a product.
     *
     * @param string $product_id
     * @param Request $request
     * @return Response
     *
     * @throws MissingProductIdException
     * @throws ProductNotFoundException
     * @throws ShopNotFoundException
     */
    public function getRecommendation(string $product_id, Request $request): Response
    {
        $shop_id = $this->getShopId();
        $product_id = $this->getProductId($shop_id, $product_id);

        $products = $this->product_recommendation_service->getRecommendedProducts(
            $shop_id,
            $product_id
        );

        return response()->success('Recommendations retrieved successfully', $products);

    }

    /**
     * Set state of the recommendation for admin.
     *
     * @param string $product_id
     * @param Request $request
     * @return Response
     *
     * @throws MissingProductIdException
     * @throws ProductNotFoundException
     * @throws ShopNotFoundException
     */
    public function setRecommendationType(string $product_id, Request $request): Response
    {
        $shop_id = $this->getShopId();
        $product_id = $this->getProductId($shop_id, $product_id);
        $type = $request->input('recommendation_type');

        $product = $this->product_recommendation_service->setRecommendationType($shop_id, $product_id, $type);

        $response_data = [
            'id' => $product->getGid(),
            'recommendation_type' => $product->getRecommendationType()
        ];
        return response()->success('Recommendation type has been set.', $response_data);
    }

    /**
     * Set list manual recommendation for a product by admin.
     *
     * @param string $product_id
     * @param Request $request
     * @return Response
     *
     * @throws MissingProductIdException
     * @throws ProductNotFoundException
     * @throws ShopNotFoundException
     */
    public function setManualRecommendation(string $product_id, Request $request): Response
    {
        $shop_id = $this->getShopId();
        $product_id = $this->getProductId($shop_id, $product_id);
        $list_recommended_gid = array_map([Utils::class, 'getIdFromGid'], $request->input('recommended_ids'));
        $recommended_type = $request->input('recommendation_type');

        $product = $this->product_recommendation_service->setRecommendedProducts(
            $shop_id,
            $product_id,
            $list_recommended_gid,
            $recommended_type
        );

        $response_data = [
            'id' => $product->getGid(),
            'recommended_ids' => $this->product_query->getListGidByIds($product->getManualIds()),
            'recommendation_type' => $product->getRecommendationType()
        ];
        return response()->success('Manual recommendation has been set.', $response_data);
    }

    /**
     * Get list manual recommendation for a product by admin.
     *
     * @param string $product_id
     * @param Request $request
     * @return Response
     *
     * @throws MissingProductIdException
     * @throws ProductNotFoundException
     * @throws ShopNotFoundException
     */
    public function getManualRecommendation(string $product_id, Request $request): Response
    {
        $shop_id = $this->getShopId();
        $product_id = $this->getProductId($shop_id, $product_id);

        $manual_gids = $this->product_recommendation_service->getManualProducts($product_id);

        return response()->success('Manual recommendation has been set.', $manual_gids);
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
