<?php

namespace App\Contracts\Commands;

use App\Collections\Shop as ShopCollection;

interface Product
{
    /**
     * Create a product of a shop with data from Shopify.
     *
     * @param array $product
     * @param ShopCollection $shop
     * @return void
     */
    public function create(array $product, ShopCollection $shop): void;


    /**
     * Create a list products of a shop with data from Shopify.
     *
     * @param array $products
     * @param ShopCollection $shop
     * @return void
     */
    public function createMany(array $products, ShopCollection $shop): void;

    /**
     * Update a product of a shop with data from Shopify.
     *
     * @param array $product
     * @param ShopCollection $shop
     * @return bool
     */
    public function update(array $product, ShopCollection $shop): bool;

    /**
     * Delete a product.
     *
     * @param string $product_id
     * @param int $shop_id
     * @return bool
     */
    public function delete(string $product_id, int $shop_id): bool;
}
