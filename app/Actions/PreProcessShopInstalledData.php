<?php

namespace App\Actions;

use App\Contracts\Commands\IProductCommand;
use App\Contracts\Commands\IShopCommand;
use App\Contracts\Objects\Transform\ShopifyTransform;
use App\Contracts\Queries\IProductQuery;
use App\Contracts\Queries\IShopQuery;
use App\Contracts\Recommendation\IProduct;
use App\Objects\Transform\ProductTransform;

class PreProcessShopInstalledData
{
    /**
     * @var IProductQuery
     */
    protected IProductQuery $productQuery;

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
     * @param IProductQuery $productQuery
     * @param IProductCommand $productCommand
     * @param IProduct $productService
     * @param IShopQuery $shopQuery
     * @param IShopCommand $shopCommand
     * @param ShopifyTransform $productTransform
     * @return void
     */
    public function __construct(
        IProductQuery $productQuery,
        IProductCommand $productCommand,
        IProduct $productService,
        IShopQuery $shopQuery,
        IShopCommand $shopCommand,
        ShopifyTransform $productTransform,
    ) {
        $this->productQuery = $productQuery;
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
        if ($isTrashed) {
            $shop = $this->shopQuery->getByDomain($domain);
            $this->productService->createOrUpdateMany($shop, $products);
        } else {
            $newShop = $this->shopCommand->create($domain);
            $this->productCommand->createMany($newShop, $products);
        }
    }
}
