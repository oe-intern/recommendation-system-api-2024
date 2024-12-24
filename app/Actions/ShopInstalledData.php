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
    protected IProductQueryShopify $productQueryShopify;

    /**
     * @var ProductTransform
     */
    protected ProductTransform $productTransform;

    /**
     * @var IRecommendationProcess
     */
    protected IRecommendationProcess $recommendationProcess;

    /**
     * @var PreProcessShopInstalledData
     */
    protected PreProcessShopInstalledData $preProcessShopInstalledData;

    /**
     * InstallShop constructor.
     *
     * @param IProductQueryShopify $productQueryShopify
     * @param ShopifyTransform $productTransform
     * @param IRecommendationProcess $recommendationProcess
     * @param PreProcessShopInstalledData $preProcessShopInstalledData
     */
    public function __construct(
        IProductQueryShopify $productQueryShopify,
        ShopifyTransform $productTransform,
        IRecommendationProcess $recommendationProcess,
        PreProcessShopInstalledData $preProcessShopInstalledData,
    ) {
        $this->productQueryShopify = $productQueryShopify;
        $this->productTransform = $productTransform;
        $this->recommendationProcess = $recommendationProcess;
        $this->preProcessShopInstalledData = $preProcessShopInstalledData;
    }

    /**
     * Process shop installed data
     *
     * @param string $domain
     * @param bool $isTrashed
     * @return void
     */
    public function __invoke(string $domain, bool $isTrashed): void
    {
        $productsData = $this->productQueryShopify->fetchAll();
        $ordersData = $this->getOrdersData($domain);
        // Install shop & product to MongoDB
        call_user_func(
            $this->preProcessShopInstalledData,
            $domain,
            $isTrashed,
            $this->productCollectionData($productsData),
        );
        // Install recommendation data for shop including default recommendation & auto recommendation
        ExecuteRecommendationPipelineJob::dispatchSync(
            $domain, $this->getProductsData($productsData),
            $ordersData,
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
        return $this->recommendationProcess->processOrderData($domain);
    }

    /**
     * Product collection data
     *
     * @param array $productsData
     * @return array
     */
    public function productCollectionData(array $productsData): array
    {
        return $this->productTransform->shopifyDataListToCollectionDataList($productsData);
    }

    /**
     * Get product data to install
     *
     * @param array $productsData
     * @return array
     */
    public function getProductsData(array $productsData): array
    {
        return $this->productTransform->shopifyDataListToModelApiListData($productsData);
    }
}
