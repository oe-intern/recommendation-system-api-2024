<?php

namespace App\Http\Controllers;

use App\Contracts\Queries\IProductQuery;
use App\Exceptions\ProductNotFoundException;
use App\Contracts\Queries\IShopQuery;
use App\Exceptions\ShopNotFoundException;
use App\Exceptions\MissingProductIdException;
use App\Services\Shopify\UserContext;
use App\Traits\ApiResponse;

class BaseController extends Controller
{
    use ApiResponse;

    /**
     * @var UserContext
     */
    protected UserContext $userContext;

    /**
     * @var IProductQuery
     */
    protected IProductQuery $productQuery;

    /**
     * @var IShopQuery
     */
    protected IShopQuery $shopQuery;

    /**
     * BaseController constructor.
     *
     * @param UserContext $userContext
     * @param IProductQuery $productQuery
     * @param IShopQuery $shopQuery
     */
    public function __construct(
        UserContext $userContext,
        IProductQuery $productQuery,
        IShopQuery $shopQuery
    ) {
        $this->userContext = $userContext;
        $this->productQuery = $productQuery;
        $this->shopQuery = $shopQuery;
    }

    /**
     * Get shop from user context and return shop ID.
     *
     * @return string
     * @throws ShopNotFoundException
     */
    protected function getShopId(): string
    {
        $shopDomain = $this->userContext->getDomain()->toNative();
        return $this->shopQuery->getShopIdByDomain($shopDomain);
    }

    /**
     * Get product Id from request
     *
     * @param string $shopId
     * @param string|null $productGid
     * @return string
     *
     * @throws ProductNotFoundException
     * @throws MissingProductIdException
     */
    protected function getProductId(string $shopId, ?string $productGid): string
    {
        if (empty($productGid)) {
            throw new MissingProductIdException();
        }

        $product = $this->productQuery->getByShopIdAndGid($shopId, $productGid);

        if (empty($product)) {
            throw new ProductNotFoundException($productGid);
        }

        return $product->getId();
    }
}
