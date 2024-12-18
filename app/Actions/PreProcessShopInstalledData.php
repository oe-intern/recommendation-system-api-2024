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
    protected IProductQuery $product_query;

    /**
     * @var IShopQuery
     */
    protected IShopQuery $shop_query;

    /**
     * @var IShopCommand
     */
    protected IShopCommand $shop_command;

    /**
     * @var IProductCommand
     */
    protected IProductCommand $product_command;

    /**
     * @var IProduct
     */
    protected IProduct $product_service;


    /**
     * @var ProductTransform
     */
    protected ProductTransform $product_transform;


    /**
     * Execute the job
     *
     * @param IProductQuery $product_query
     * @param IProductCommand $product_command
     * @param IProduct $product_service
     * @param IShopQuery $shop_query
     * @param IShopCommand $shop_command
     * @param ShopifyTransform $product_transform
     * @return void
     */
    public function __construct(
        IProductQuery $product_query,
        IProductCommand $product_command,
        IProduct $product_service,
        IShopQuery $shop_query,
        IShopCommand $shop_command,
        ShopifyTransform $product_transform,
    ) {
        $this->product_query = $product_query;
        $this->product_command = $product_command;
        $this->product_service = $product_service;
        $this->shop_query = $shop_query;
        $this->shop_command = $shop_command;
        $this->product_transform = $product_transform;
    }

    /**
     * @param string $domain
     * @param bool $is_trashed
     * @param array $products
     * @return void
     */
    public function __invoke(string $domain, bool $is_trashed, array $products): void
    {
        $products_data = $this->product_transform->shopifyDataListToCollectionDataList($products);

        if ($is_trashed) {
            $shop = $this->shop_query->getByDomain($domain);
            $this->product_service->createOrUpdateMany($shop, $products_data);
        } else {
            $new_shop = $this->shop_command->create($domain);
            $this->product_command->createMany($new_shop, $products_data);
        }
    }
}
