<?php

namespace App\Contracts\Queries;

use App\Collections\Product as ProductCollection;

interface RelationshipScore
{
    /**
     * Get list relationship score of a product from a shop.
     *
     * @param string $product_id
     * @return array
     */
    public function getByProductId(string $product_id): array;

    /**
     * Get list relationship score of a product from a shop.
     *
     * @param ProductCollection $product
     * @return array
     */
    public function getByProductCollection(ProductCollection $product): array;

    /**
     * Get list relationship score of a product from a shop.
     *
     * @param string $shop_domain
     * @param string $product_id
     * @return array
     */
    public function getByShopDomainAndProductId(string $shop_domain, string $product_id): array;
}
