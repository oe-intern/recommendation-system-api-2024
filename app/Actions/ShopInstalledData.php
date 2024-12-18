<?php


namespace App\Actions;

use App\Contracts\Shopify\Graphql\Queries\IProductQueryShopify;
use App\Jobs\ExecuteRecommendationPipelineJob;

class ShopInstalledData
{
    /**
     * @var IProductQueryShopify
     */
    protected IProductQueryShopify $product_query_shopify;

    /**
     * @var PreProcessShopInstalledData
     */
    protected PreProcessShopInstalledData $pre_process_shop_installed_data;

    /**
     * InstallShop constructor.
     */
    public function __construct(
        IProductQueryShopify $product_query_shopify,
        PreProcessShopInstalledData $pre_process_shop_installed_data,
    ) {
        $this->product_query_shopify = $product_query_shopify;
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
        // Install shop & product to MongoDB
        call_user_func($this->pre_process_shop_installed_data, $domain, $is_trashed, $products_data);
        // Install recommendation data for shop including default recommendation & auto recommendation
        ExecuteRecommendationPipelineJob::dispatchSync($domain, $products_data);
    }
}
