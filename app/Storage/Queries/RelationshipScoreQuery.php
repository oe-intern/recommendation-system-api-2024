<?php

namespace App\Storage\Queries;

use App\Collections\ProductCollection;
use App\Contracts\Queries\IRelationshipScoreQuery;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RelationshipScoreQuery implements IRelationshipScoreQuery
{
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

    /**
     * Get list of relationship scores of a product from a shop.
     *
     * @param string $product_id
     * @return array
     *
     * @throws ModelNotFoundException
     */
    public function getByProductId(string $product_id): array
    {
        return ProductCollection::query()
            ->where('id', $product_id)
            ->firstOrFail()
            ->relationshipScore()
            ->get()
            ->toArray();
    }

    /**
     * Get list of relationship scores of a product from a shop.
     *
     * @param string $shop_domain
     * @param string $product_id
     * @return array
     *
     * @throws ModelNotFoundException
     */
    public function getByShopDomainAndProductId(string $shop_domain, string $product_id): array
    {
        return ProductCollection::query()
            ->where('id', $product_id)
            ->where('shop_collection_domain', $shop_domain)
            ->firstOrFail()
            ->relationshipScore()
            ->get()
            ->toArray();
    }
}
