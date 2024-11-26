<?php

namespace App\Http\Controllers;

use App\Contracts\Queries\Product;
use App\Contracts\Recommendation\ProductRecommendation as ProductRecommendationService;
use App\Exceptions\ProductNotFoundException;
use App\Lib\Utils;
use App\Objects\Enums\RecommendationType;
use App\Services\Shopify\UserContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    protected UserContext $user_context;
    protected ProductRecommendationService $product_recommendation_service;
    protected Product $product_query;

    public function __construct(
        UserContext $user_context,
        ProductRecommendationService $product_recommendation_service,
        Product $product_query
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
        $product_id = $request->query('product_id');

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
        $product_id = $request->input('product_id');
        $type = RecommendationType::tryFrom($request->input('recommendation_type'));

        $this->validateProductIds($shop_domain, [$product_id]);

        $product = $this->product_recommendation_service->setRecommendationType($product_id, $type);

        return response()->json([
            'message' => 'Recommendation state has been set.',
            'product' => $product
        ]);
    }

    /**
     * Validate product IDs exist in the shop.
     *
     * @param string $shop_domain
     * @param array $product_ids
     * @return void
     * @throws ProductNotFoundException
     */
    private function validateProductIds(string $shop_domain, array $product_ids): void
    {
        $existing_product_ids = $this->product_query->getByShopDomainAndIds($shop_domain, $product_ids);

        $not_existing = array_diff($product_ids, $existing_product_ids);
        if (!empty($not_existing)) {
            throw new ProductNotFoundException($shop_domain, $not_existing);
        }
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
        $recommended_type = RecommendationType::tryFrom($request->input('recommendation_type'));
        $filter_product_ids = array_merge([$product_id], $recommended_ids);

        $this->validateProductIds($shop_domain, $filter_product_ids);

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

        $this->validateProductIds($shop_domain, [$product_id]);

        $product = $this->product_recommendation_service->getFullInfo($product_id);

        return response()->json([
            'product' => $product
        ]);
    }
}
