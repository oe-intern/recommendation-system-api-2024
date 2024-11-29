<?php

namespace App\Http\Controllers;

use App\Contracts\Queries\IProductQuery;
use App\Contracts\Recommendation\IProductRecommendation;
use App\Exceptions\ProductNotFoundException;
use App\Lib\Utils;
use App\Services\Shopify\UserContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    /**
     * @var UserContext
     */
    protected UserContext $user_context;

    /**
     * @var IProductRecommendation
     */
    protected IProductRecommendation $product_recommendation_service;

    /**
     * @var IProductQuery
     */
    protected IProductQuery $product_query;

    /**
     * RecommendationController constructor.
     *
     * @param UserContext $user_context
     * @param IProductRecommendation $product_recommendation_service
     * @param IProductQuery $product_query
     */
    public function __construct(
        UserContext $user_context,
        IProductRecommendation $product_recommendation_service,
        IProductQuery $product_query
    ) {
        $this->user_context = $user_context;
        $this->product_recommendation_service = $product_recommendation_service;
        $this->product_query = $product_query;
    }

    /**
     * Get list of recommendations for a product.
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @throws ProductNotFoundException
     */
    public function getRecommendation(Request $request): JsonResponse
    {
        $shop_domain = $this->user_context->getDomain()->toNative();
        $product_id = Utils::getIdFromGid($request->query('product_id'));

        if (empty($product_id)) {
            return response()->json([
                'message' => 'Product ID is required.'
            ], 400);
        }

        $products = $this->product_recommendation_service->getRecommendedProducts(
            $shop_domain,
            $product_id
        );

        return response()->json([
            'products' => $products
        ]);

    }

    /**
     * Set state of the recommendation for admin.
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @throws ProductNotFoundException
     */
    public function setState(Request $request): JsonResponse
    {
        $shop_domain = $this->user_context->getDomain()->toNative();
        $product_id = Utils::getIdFromGid($request->input('product_id'));
        $type = $request->input('recommendation_type');

        $product = $this->product_recommendation_service->setRecommendationType($shop_domain, $product_id, $type);

        return response()->json([
            'message' => 'Recommendation state has been set.',
            'product' => $product
        ]);
    }

    /**
     * Set list manual recommendation for a product by admin.
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @throws ProductNotFoundException
     */
    public function setManualRecommendation(Request $request): JsonResponse
    {
        $shop_domain = $this->user_context->getDomain()->toNative();
        $product_id = Utils::getIdFromGid($request->input('product_id'));
        $recommended_ids = array_map([Utils::class, 'getIdFromGid'], $request->input('recommended_ids'));
        $recommended_type = $request->input('recommendation_type');

        $product = $this->product_recommendation_service->setRecommendedProducts(
            $shop_domain,
            $product_id,
            $recommended_ids,
            $recommended_type
        );

        return response()->json([
            'message' => 'Recommendation has been set.',
            'product' => $product
        ]);
    }

    /**
     * Get product information by ID (including recommendation products).
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @throws ProductNotFoundException
     */
    public function getProduct(Request $request): JsonResponse
    {
        $shop_domain = $this->user_context->getDomain()->toNative();
        $product_id = Utils::getIdFromGid($request->query('product_id'));

        if (empty($product_id)) {
            return response()->json([
                'message' => 'Product ID is required.'
            ], 400);
        }

        $product = $this->product_recommendation_service->getFullInfo($shop_domain, $product_id);

        return response()->json([
            'product' => $product
        ]);
    }

    public function getAutoRecommendation(Request $request): JsonResponse
    {
        $shop_domain = $this->user_context->getDomain()->toNative();
        $settings = $this->product_recommendation_service->getAutoRecommendationSettings($shop_domain);

        return response()->json($settings);
    }

    /**
     * Set auto recommendation settings for a shop.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function setAutoRecommendation(Request $request): JsonResponse
    {
        $shop_domain = $this->user_context->getDomain()->toNative();
        $settings = [
            'number_of_items' => $request->input('number_of_items'),
            'layout' => $request->input('layout'),
            'background_color' => $request->input('background_color'),
            'text_color' => $request->input('text_color')
        ];

        $settings = $this->product_recommendation_service->setAutoRecommendationSettings($shop_domain, $settings);

        return response()->json([
            'message' => 'Auto recommendation settings has been set.',
            'settings' => $settings
        ]);
    }
}
