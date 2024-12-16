<?php

namespace App\Services\Recommendation;

use App\Collections\ShopCollection;
use App\Contracts\Commands\IProductCommand;
use App\Contracts\Queries\IProductQuery;
use App\Contracts\Recommendation\IProduct;

class ProductService implements IProduct
{
    /**
     * @var IProductQuery
     */
    protected IProductQuery $product_query;

    /**
     * @var IProductCommand
     */
    protected IProductCommand $product_command;

    /**
     * ProductService constructor.
     *
     * @param IProductQuery $product_query
     * @param IProductCommand $product_command
     */
    public function __construct(IProductQuery $product_query, IProductCommand $product_command)
    {
        $this->product_query = $product_query;
        $this->product_command = $product_command;
    }

    /**
     * Handle update product if exist and create if not
     *
     * @param ShopCollection $shop
     * @param array $product_data
     * @return void
     */
    public function createOrUpdateMany(ShopCollection $shop, array $product_data): void
    {
        $products_gid = array_column($product_data, 'gid');
        $exist_products = $this->product_query->getExistingProductsGid($shop->getId(), $products_gid);

        $exist_products_data = array_filter($product_data, function ($product) use ($exist_products) {
            return in_array($product['gid'], $exist_products);
        });
        $new_products_data = array_filter($product_data, function ($product) use ($exist_products) {
            return !in_array($product['gid'], $exist_products);
        });

        $this->product_command->updateManyByGid($exist_products_data);
        $this->product_command->createMany($shop, $new_products_data);
    }
}
