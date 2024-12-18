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

class ProductRecommendationController extends BaseController
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
            $product_id,
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
            'recommendation_type' => $product->getRecommendationType(),
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
            $recommended_type,
        );

        $response_data = [
            'id' => $product->getGid(),
            'recommended_ids' => $this->product_query->getListGidByIds($product->getManualIds()),
            'recommendation_type' => $product->getRecommendationType(),
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

    public function getRecommendationTypes()
    {
        // TODO: Implement getRecommendationTypes() method.
    }
}
