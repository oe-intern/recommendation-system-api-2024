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

        $response = $this->productRecommendationService->getRecommendedProducts(
            $shopId,
            $productId,
        );

        return $this->successResponse(
            'Recommendations retrieved successfully',
            $response->toArray()
        );
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

        $response = $this->productRecommendationService->setRecommendationType(
            $shopId,
            $productId,
            $setRecommendationTypeRequestDTO,
        );

        return $this->successResponse(
            'Recommendation type has been set.',
            $response->toArray()
        );
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

        $response = $this->productRecommendationService->setRecommendedProducts(
            $shopId,
            $productId,
            $setProductRecommendationRequestDTO,
        );

        return $this->successResponse(
            'Manual recommendation has been set.',
            $response->toArray()
        );
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

        $response = $this->productRecommendationService->getManualProducts($productId);

        return $this->successResponse(
            'Manual recommendation has been set.',
            $response->toArray()
        );
    }

    /**
     * Get product recommendation types.
     *
     * @param string $productId
     * @param Request $request
     * @return Response
     *
     * @throws MissingProductIdException
     * @throws ProductNotFoundException
     * @throws ShopNotFoundException
     */
    public function getRecommendationType(string $productId, Request $request): Response
    {
        $shopId = $this->getShopId();
        $productId = $this->getProductId($shopId, $productId);

        $response = $this->productRecommendationService->getProductRecommendationType($shopId, $productId);

        return $this->successResponse(
            'Recommendation types retrieved successfully',
            $response->toArray()
        );
    }
}
