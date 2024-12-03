<?php

namespace App\Http\Controllers;

use App\Contracts\Queries\IProductQuery;
use App\Exceptions\ProductNotFoundException;
use App\Contracts\Queries\IShopQuery;
use App\Exceptions\ShopNotFoundException;
use App\Exceptions\MissingProductIdException;
use App\Services\Shopify\UserContext;

class BaseController extends Controller
{
    /**
     * @var UserContext
     */
    protected UserContext $user_context;

    /**
     * @var IProductQuery
     */
    protected IProductQuery $product_query;

    /**
     * @var IShopQuery
     */
    protected IShopQuery $shop_query;

    /**
     * BaseController constructor.
     *
     * @param UserContext $user_context
     * @param IProductQuery $product_query
     * @param IShopQuery $shop_query
     */
    public function __construct(
        UserContext $user_context,
        IProductQuery $product_query,
        IShopQuery $shop_query
    ) {
        $this->user_context = $user_context;
        $this->product_query = $product_query;
        $this->shop_query = $shop_query;
    }

    /**
     * Get shop from user context and return shop ID.
     *
     * @return string
     * @throws ShopNotFoundException
     */
    protected function getShopId(): string
    {
        $shop_domain = $this->user_context->getDomain()->toNative();
        return $this->shop_query->getShopIdByDomain($shop_domain);
    }

    /**
     * Get product Id from request
     *
     * @param string $shop_id
     * @param string|null $product_gid
     * @return string
     *
     * @throws ProductNotFoundException
     * @throws MissingProductIdException
     */
    protected function getProductId(string $shop_id, ?string $product_gid): string
    {
        if (empty($product_gid)) {
            throw new MissingProductIdException();
        }

        $product = $this->product_query->getByShopIdAndGid($shop_id, $product_gid);
        if (empty($product)) {
            throw new ProductNotFoundException($product_gid);
        }
        return $product->getId();
    }
}
