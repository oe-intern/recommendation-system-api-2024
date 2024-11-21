<?php

namespace App\Contracts\Queries;

use App\Collections\Product as ProductCollection;
use App\Collections\Shop as ShopCollection;

interface Product
{
    /**
     * Get a product of a shop by ID.
     *
     * @param string $product_id
     * @param ShopCollection $shop
     * @return ProductCollection|null
     */
    public function getByIdAndShopCollection(string $product_id, ShopCollection $shop): ?ProductCollection;

    /**
     * Get list products of a shop by IDs
     *
     * @param array $product_ids
     * @param ShopCollection $shop
     * @return array
     */
    public function getByIdsAndShopCollection(array $product_ids, ShopCollection $shop): array;

    /**
     * Get a product of a shop by shop domain and product ID.
     */
    public function getByShopDomainAndProductId(string $shop_domain, string $product_id): ?ProductCollection;

    /**
     * Get list products ID using this product for recommendation.
     *
     * @param ShopCollection $shop
     * @param string $product_id
     * @return array
     */
    public function getReferencedProducts(ShopCollection $shop, string $product_id): array;
}
