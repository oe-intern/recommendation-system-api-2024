<?php


namespace App\Actions;

use App\Contracts\Objects\Transform\ShopifyTransform;
use App\Contracts\Recommendation\IRecommendationProcess;
use App\Objects\Transform\ProductTransform;
use App\Contracts\Shopify\Graphql\Queries\IProductQueryShopify;
use App\Jobs\ExecuteRecommendationPipelineJob;

class ShopInstalledData
{
    /**
     * @var IProductQueryShopify
     */
    protected IProductQueryShopify $product_query_shopify;

    /**
     * @var ProductTransform
     */
    protected ProductTransform $product_transform;

    /**
     * @var IRecommendationProcess
     */
    protected IRecommendationProcess $recommendation_process;

    /**
     * @var PreProcessShopInstalledData
     */
    protected PreProcessShopInstalledData $pre_process_shop_installed_data;

    /**
     * InstallShop constructor.
     *
     * @param IProductQueryShopify $product_query_shopify
     * @param ShopifyTransform $product_transform
     * @param IRecommendationProcess $recommendation_process
     * @param PreProcessShopInstalledData $pre_process_shop_installed_data
     */
    public function __construct(
        IProductQueryShopify $product_query_shopify,
        ShopifyTransform $product_transform,
        IRecommendationProcess $recommendation_process,
        PreProcessShopInstalledData $pre_process_shop_installed_data,
    ) {
        $this->product_query_shopify = $product_query_shopify;
        $this->product_transform = $product_transform;
        $this->recommendation_process = $recommendation_process;
        $this->pre_process_shop_installed_data = $pre_process_shop_installed_data;
    }

    /**
     * Process shop installed data
     *
     * @param string $domain
     * @param bool $is_trashed
     * @return void
     */
    public function __invoke(string $domain, bool $is_trashed): void
    {
        $products_data = $this->product_query_shopify->fetchAll();
        $orders_data = $this->getOrdersData($domain);
        // Install shop & product to MongoDB
        call_user_func(
            $this->pre_process_shop_installed_data,
            $domain,
            $is_trashed,
            $this->productCollectionData($products_data),
        );
        // Install recommendation data for shop including default recommendation & auto recommendation
        ExecuteRecommendationPipelineJob::dispatchSync(
            $domain, $this->getProductsData($products_data),
            $orders_data,
        );
    }

    /**
     * Get order data to install
     *
     * @param string $domain
     * @return array
     */
    public function getOrdersData(string $domain): array
    {
        return $this->recommendation_process->processOrderData($domain);
    }

    /**
     * Product collection data
     *
     * @param array $products_data
     * @return array
     */
    public function productCollectionData(array $products_data): array
    {
        return $this->product_transform->shopifyDataListToCollectionDataList($products_data);
    }

    /**
     * Get product data to install
     *
     * @param array $products_data
     * @return array
     */
    public function getProductsData(array $products_data): array
    {
        return $this->product_transform->shopifyDataListToModelApiListData($products_data);
    }
}
