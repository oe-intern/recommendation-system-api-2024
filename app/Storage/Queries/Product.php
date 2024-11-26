<?php

namespace App\Storage\Queries;

use App\Collections\Product as ProductCollection;
use App\Contracts\Queries\Product as ProductCommand;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Product implements ProductCommand
{
    /**
     * Get a product of a shop by ID.
     *
     * @param string $product_id
     * @return ProductCollection|null
     */
    public function getById(string $product_id): ?ProductCollection
    {
        return ProductCollection::query()->find($product_id);
    }

    /**
     * Get list products of a shop by IDs
     *
     * @param array $product_ids
     * @param string $shop_domain
     * @return array
     */
    public function getByShopDomainAndIds(string $shop_domain, array $product_ids): array
    {
        $products = ProductCollection::query()
            ->whereIn('id', $product_ids)
            ->where('shop_domain', $shop_domain)
            ->get()
            ->all();

        return collect($products)->pluck('id')->toArray();
    }

    /**
     * Check if the product exist in the database.
     *
     * @param string $shop_domain
     * @param string $product_id
     * @return ProductCollection
     *
     * @throws ModelNotFoundException
     */
    public function checkProductExist(string $shop_domain, string $product_id): ProductCollection
    {
        return ProductCollection::query()
            ->where('id', $product_id)
            ->where('shop_domain', $shop_domain)
            ->firstOrFail();
    }

    /**
     * Get a product of a shop by shop domain and product ID.
     *
     * @param string $shop_domain
     * @param string $product_id
     * @return ProductCollection|null
     */
    public function getByShopDomainAndId(string $shop_domain, string $product_id): ?ProductCollection
    {
        return ProductCollection::query()
            ->where('id', $product_id)
            ->where('shop_domain', $shop_domain)
            ->first();
    }

    /**
     * Get list of referenced products for recommendation.
     *
     * @param string $product_id
     * @return array
     */
    public function getReferencedProducts(string $product_id): array
    {
        return $this->getProductRecommendationIds($product_id, 'referencedIds');
    }

    /**
     * Get list of optional products for recommendation.
     *
     * @param string $product_id
     * @return array
     */
    public function getOptionalProducts(string $product_id): array
    {
        return $this->getProductRecommendationIds($product_id, 'optionIds');
    }

    /**
     * Get list of product IDs using a specific attribute for recommendation.
     *
     * @param string $product_id
     * @param string $attribute
     * @return array
     */
    private function getProductRecommendationIds(string $product_id, string $attribute): array
    {
        return ProductCollection::query()
            ->where('id', $product_id)
            ->first()
            ->getAttributeValue($attribute);
    }
}
