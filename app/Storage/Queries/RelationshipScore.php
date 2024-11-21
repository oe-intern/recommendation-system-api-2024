<?php

namespace App\Storage\Queries;

use App\Collections\Product as ProductCollection;
use App\Collections\Shop as ShopCollection;
use App\Contracts\Queries\Product as ProductQuery;
use App\Contracts\Queries\RelationshipScore as RelationshipQuery;

class RelationshipScore implements RelationshipQuery
{
    /**
     * @var ProductQuery
     */
    protected ProductQuery $product_query;

    /**
     * RelationshipScore constructor.
     *
     * @param ProductQuery $product_query
     */
    public function __construct(ProductQuery $product_query)
    {
        $this->product_query = $product_query;
    }

    /**
     * Get list of relationship scores of a product from a shop.
     *
     * @param ShopCollection $shop
     * @param string $product_id
     * @return array
     */
    public function getByShopCollectionAndId(
        ShopCollection $shop,
        string $product_id,
    ): array {
        $product = $this->product_query->getByIdAndShopCollection($product_id, $shop);

        if (!$product) {
            return [];
        }

        return $product->relationshipScore()->get()->toArray();
    }

    /**
     * Get list of relationship scores of a product.
     *
     * @param ProductCollection $product
     * @return array
     */
    public function getByProductCollection(ProductCollection $product): array
    {
        return $product->relationshipScore()->get()->toArray();
    }
}
