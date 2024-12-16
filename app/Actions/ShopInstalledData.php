<?php


namespace App\Actions;

use App\Contracts\ModelRecommendation\IRecommendationApi;
use App\Contracts\Queries\IProductQuery;
use App\Contracts\Queries\IShopQuery;
use App\Contracts\Recommendation\IProductRecommendation;
use App\Contracts\Recommendation\IRecommendationProcess;
use App\Contracts\Shopify\Graphql\Queries\IOrderQueryShopify;
use App\Contracts\Shopify\Graphql\Queries\IProductQueryShopify;
use App\Jobs\PreProcessShopInstalledData;
use App\Jobs\ProcessShopInstalledData;
use App\Objects\Transform\ProductTransform;
use Exception;

class ShopInstalledData
{
    /**
     * @var int
     */
    private int $MAX_RECOMMENDATION_PRODUCTS = 6;

    /**
     * @var IProductQueryShopify
     */
    protected IProductQueryShopify $product_query_shopify;

    /**
     * @var IOrderQueryShopify
     */
    protected IOrderQueryShopify $order_query_shopify;

    /**
     * @var IRecommendationProcess
     */
    protected IRecommendationProcess $recommendation_process;

    /**
     * @var IProductQuery
     */
    protected IProductQuery $product_query;

    /**
     * @var IShopQuery
     */
    protected IShopQuery $shop_query;

    /**
     * @var IProductRecommendation
     */
    protected IProductRecommendation $product_recommendation_service;

    /**
     * @var IRecommendationApi
     */
    protected IRecommendationApi $recommendation_api_service;

    /**
     * @var ProductTransform
     */
    protected ProductTransform $product_transform;

    /**
     * InstallShop constructor.
     */
    public function __construct(
        IProductQueryShopify $product_query_shopify,
        IOrderQueryShopify $order_query_shopify,
        IRecommendationProcess $recommendation_process,
        IProductQuery $product_query,
        IShopQuery $shop_query,
        IProductRecommendation $product_recommendation_service,
        IRecommendationApi $recommendation_api_service,
        ProductTransform $product_transform,
    ) {
        $this->product_query_shopify = $product_query_shopify;
        $this->order_query_shopify = $order_query_shopify;
        $this->recommendation_process = $recommendation_process;
        $this->product_query = $product_query;
        $this->shop_query = $shop_query;
        $this->product_recommendation_service = $product_recommendation_service;
        $this->recommendation_api_service = $recommendation_api_service;
        $this->product_transform = $product_transform;
    }

    /**
     * Process shop installed data
     *
     * @param string $domain
     * @param bool $is_trashed
     * @return void
     *
     * @throws Exception
     */
    public function __invoke(string $domain, bool $is_trashed): void
    {
        $products_data = $this->product_query_shopify->fetchAll();
        $products_process_data = $this->product_transform->shopifyDataListToModelApiListData($products_data);

        PreProcessShopInstalledData::dispatchSync($domain, $is_trashed, $products_data);

        $orders_process_data = $this->recommendation_process->processOrderData($domain);
        $data_request = $this->getData($products_process_data, $orders_process_data);
        $shop = $this->shop_query->getByDomain($domain);
        $map_gid_id = $this->product_query->getMapIdWithKeyGidByShopId($shop->getId());

        $recommendation_data = $this->recommendation_api_service->preRecommend($data_request);
        $this->product_recommendation_service->updateManyDefaultRecommendation($recommendation_data, $map_gid_id);

        ProcessShopInstalledData::dispatch($map_gid_id, $products_data, $data_request);
    }

    /**
     * Get data to request recommendation api
     *
     * @param array $products_data
     * @param array $orders_process_data
     * @return array
     */
    private function getData(array $products_data, array $orders_process_data): array
    {
        return array_merge([
                'products' => $products_data,
                'number_of_items' => $this->MAX_RECOMMENDATION_PRODUCTS
            ], $orders_process_data);
    }
}
