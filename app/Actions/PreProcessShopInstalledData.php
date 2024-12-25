<?php

namespace App\Actions;

use App\Contracts\Commands\IProductCommand;
use App\Contracts\Commands\IShopCommand;
use App\Contracts\Objects\Transform\ShopifyTransform;
use App\Contracts\Queries\IShopQuery;
use App\Contracts\Recommendation\IProduct;
use App\Objects\Transform\ProductTransform;

class PreProcessShopInstalledData
{
    /**
     * @var IShopQuery
     */
    protected IShopQuery $shopQuery;

    /**
     * @var IShopCommand
     */
    protected IShopCommand $shopCommand;

    /**
     * @var IProductCommand
     */
    protected IProductCommand $productCommand;

    /**
     * @var IProduct
     */
    protected IProduct $productService;

    /**
     * @var ProductTransform
     */
    protected ProductTransform $productTransform;

    /**
     * Execute the job
     *
     * @param IProductCommand $productCommand
     * @param IProduct $productService
     * @param IShopQuery $shopQuery
     * @param IShopCommand $shopCommand
     * @param ShopifyTransform $productTransform
     * @return void
     */
    public function __construct(
        IProductCommand $productCommand,
        IProduct $productService,
        IShopQuery $shopQuery,
        IShopCommand $shopCommand,
        ShopifyTransform $productTransform,
    ) {
        $this->productCommand = $productCommand;
        $this->productService = $productService;
        $this->shopQuery = $shopQuery;
        $this->shopCommand = $shopCommand;
        $this->productTransform = $productTransform;
    }

    /**
     * @param string $domain
     * @param bool $isTrashed
     * @param array $products
     * @return void
     */
    public function __invoke(string $domain, bool $isTrashed, array $products): void
    {
        $transformedProducts = $this->productTransform->shopifyDataListToCollectionDataList($products);

        $isTrashed
            ? $this->restoreShop($domain, $transformedProducts)
            : $this->createNewShop($domain, $transformedProducts);
    }

    /**
     * Handle the case where the shop is trashed.
     *
     * @param string $domain
     * @param array $products
     * @return void
     */
    private function restoreShop(string $domain, array $products): void
    {
        $shop = $this->shopQuery->getByDomain($domain);
        $this->productService->createOrUpdateMany($shop, $products);
    }

    /**
     * Handle the case where the shop is new.
     *
     * @param string $domain
     * @param array $products
     * @return void
     */
    private function createNewShop(string $domain, array $products): void
    {
        $newShop = $this->shopCommand->create($domain);
        $this->productCommand->createMany($newShop, $products);
    }
}
