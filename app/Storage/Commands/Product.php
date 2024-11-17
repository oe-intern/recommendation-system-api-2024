<?php

namespace App\Storage\Commands;

use App\Collections\Shop as ShopCollection;
use App\Contracts\Commands\Product as ProductCommand;
use App\Objects\Transform\Product as ProductTransform;

class Product implements ProductCommand
{
    /**
     * @var ProductTransform
     */
    protected ProductTransform $product_transform;

    /**
     * Product constructor.
     *
     * @param ProductTransform $product_transform
     */
    public function __construct(ProductTransform $product_transform)
    {
        $this->product_transform = $product_transform;
    }

    /**
     * Create a product.
     *
     * @param array $product
     * @param ShopCollection $shop
     * @return void
     */
    public function create(array $product, ShopCollection $shop): void
    {
        $shop->products()->create($this->product_transform->shopifyDataToCollectionData($product));
    }

    /**
     * Create a list products of a shop with data from Shopify.
     *
     * @param array $products
     * @param ShopCollection $shop
     * @return void
     */
    public function createMany(array $products, ShopCollection $shop): void
    {
        $shop->products()->createMany($this->product_transform->shopifyDataListToCollectionDataList($products));
    }

    /**
     * Update a product of a shop with data from Shopify.
     *
     * @param array $product
     * @param ShopCollection $shop
     * @return bool
     */
    public function update(array $product, ShopCollection $shop): bool
    {
        // TODO: Implement update() method.
    }

    /**
     * Delete a product of a shop.
     *
     * @param string $product_id
     * @param int $shop_id
     * @return bool
     */
    public function delete(string $product_id, int $shop_id): bool
    {
        // TODO: Implement delete() method.
    }
}
