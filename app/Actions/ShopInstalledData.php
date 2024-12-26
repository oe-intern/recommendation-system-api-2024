<?php


namespace App\Actions;

use App\Contracts\Shopify\Graphql\Queries\IProductQueryShopify;
use App\Jobs\ExecuteRecommendationPipelineJob;

class ShopInstalledData
{
    /**
     * @var IProductQueryShopify
     */
    protected IProductQueryShopify $productQueryShopify;

    /**
     * @var PreProcessShopInstalledData
     */
    protected PreProcessShopInstalledData $preProcessShopInstalledData;

    /**
     * InstallShop constructor.
     *
     * @param IProductQueryShopify $productQueryShopify
     * @param PreProcessShopInstalledData $preProcessShopInstalledData
     */
    public function __construct(
        IProductQueryShopify $productQueryShopify,
        PreProcessShopInstalledData $preProcessShopInstalledData,
    ) {
        $this->productQueryShopify = $productQueryShopify;
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
        $productsData = $this->fetchProducts();

        $this->preProcessShopData($domain, $isTrashed, $productsData);

        $this->dispatchRecommendationJob($domain, $productsData);
    }

    /**
     * Fetch products data from Shopify
     *
     * @return array
     */
    private function fetchProducts(): array
    {
        return $this->productQueryShopify->fetchAll();
    }

    /**
     * Process shop and product data.
     *
     * @param string $domain
     * @param bool $isTrashed
     * @param array $productsData
     * @return void
     */
    private function preProcessShopData(string $domain, bool $isTrashed, array $productsData): void
    {
        call_user_func(
            $this->preProcessShopInstalledData,
            $domain,
            $isTrashed,
            $productsData,
        );
    }

    /**
     * Dispatch the recommendation pipeline job.
     *
     * @param string $domain
     * @param array $productsData
     * @return void
     */
    private function dispatchRecommendationJob(string $domain, array $productsData): void
    {
        ExecuteRecommendationPipelineJob::dispatchSync(
            $domain,
            $productsData,
        )->onQueue(config('queue.queues.recommendation'));
    }
}
