<?php

namespace App\Contracts\Queries;

use App\Collections\Product as ProductCollection;
use App\Collections\Shop as ShopCollection;

interface RelationshipScore
{
    /**
     * Get list relationship score of a product from a shop.
     *
     * @param ShopCollection $shop
     * @param string $product_id
     * @return array
     */
    public function getByShopCollectionAndId(ShopCollection $shop, string $product_id): array;

    /**
     * Get list relationship score of a product from a shop.
     *
     * @param ProductCollection $product
     * @return array
     */
    public function getByProductCollection(ProductCollection $product): array;
}
