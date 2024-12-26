<?php

namespace App\Http\Controllers;

use App\Contracts\Queries\IProductQuery;
use App\Contracts\Queries\IShopQuery;
use App\Contracts\Recommendation\IProductRecommendation;
use App\DTO\Request\SetProductRecommendationRequestDTO;
use App\DTO\Request\SetRecommendationTypeRequestDTO;
use App\Exceptions\MissingProductIdException;
use App\Exceptions\ProductNotFoundException;
use App\Exceptions\ShopNotFoundException;
use App\Http\Requests\SetProductRecommendationRequest;
use App\Http\Requests\SetRecommendationTypeRequest;
use App\Lib\Utils;
use App\Services\Shopify\UserContext;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductRecommendationController extends BaseController
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
     * Get list of recommendations for a product.
     *
     * @param string $productId
     * @param Request $request
     * @return Response
     *
     * @throws MissingProductIdException
     * @throws ProductNotFoundException
     * @throws ShopNotFoundException
     */
    public function getRecommendation(string $productId, Request $request): Response
    {
        $shopId = $this->getShopId();
        $productId = $this->getProductId($shopId, $productId);

        $products = $this->productRecommendationService->getRecommendedProducts(
            $shopId,
            $productId,
        );

        return response()->success('Recommendations retrieved successfully', $products);
    }

    /**
     * Set state of the recommendation for admin.
     *
     * @param string $productId
     * @param SetRecommendationTypeRequest $request
     * @return Response
     *
     * @throws MissingProductIdException
     * @throws ProductNotFoundException
     * @throws ShopNotFoundException
     */
    public function setRecommendationType(string $productId, SetRecommendationTypeRequest $request): Response
    {
        $shopId = $this->getShopId();
        $productId = $this->getProductId($shopId, $productId);
        $setRecommendationTypeRequestDTO = SetRecommendationTypeRequestDTO::fromRequest($request);

        $product = $this->productRecommendationService->setRecommendationType(
            $shopId,
            $productId,
            $setRecommendationTypeRequestDTO,
        );

        $responseData = [
            'id' => $product->getGid(),
            'recommendation_type' => $product->getRecommendationType(),
        ];
        return response()->success('Recommendation type has been set.', $responseData);
    }

    /**
     * Set list manual recommendation for a product by admin.
     *
     * @param string $productId
     * @param SetProductRecommendationRequest $request
     * @return Response
     *
     * @throws MissingProductIdException
     * @throws ProductNotFoundException
     * @throws ShopNotFoundException
     */
    public function setManualRecommendation(string $productId, SetProductRecommendationRequest $request): Response
    {
        $shopId = $this->getShopId();
        $productId = $this->getProductId($shopId, $productId);
        $setProductRecommendationRequestDTO = SetProductRecommendationRequestDTO::fromRequest($request);
        $setProductRecommendationRequestDTO->recommendedIds = array_map(
            [Utils::class, 'getIdFromGid'],
            $setProductRecommendationRequestDTO->recommendedIds,
        );

        $product = $this->productRecommendationService->setRecommendedProducts(
            $shopId,
            $productId,
            $setProductRecommendationRequestDTO,
        );

        $responseData = [
            'id' => $product->getGid(),
            'recommended_ids' => $this->productQuery->getListGidByIds($product->getManualIds()),
            'recommendation_type' => $product->getRecommendationType(),
        ];
        return response()->success('Manual recommendation has been set.', $responseData);
    }

    /**
     * Get list manual recommendation for a product by admin.
     *
     * @param string $productId
     * @param Request $request
     * @return Response
     *
     * @throws MissingProductIdException
     * @throws ProductNotFoundException
     * @throws ShopNotFoundException
     */
    public function getManualRecommendation(string $productId, Request $request): Response
    {
        $shopId = $this->getShopId();
        $productId = $this->getProductId($shopId, $productId);

        $manualGids = $this->productRecommendationService->getManualProducts($productId);

        return response()->success('Manual recommendation has been set.', $manualGids);
    }

    public function getRecommendationTypes()
    {
        // TODO: Implement getRecommendationTypes() method.
    }
}
