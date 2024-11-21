<?php

namespace App\Storage\Queries;

use App\Collections\Product as ProductCollection;
use App\Collections\Shop as ShopCollection;
use App\Contracts\Queries\Product as ProductCommand;

class Product implements ProductCommand
{
    /**
     * Get list products of a shop by IDs
     *
     * @param array $product_ids
     * @param ShopCollection $shop
     * @return array
     */
    public function getByIdsAndShopCollection(array $product_ids, ShopCollection $shop): array
    {
        return $shop->products()->filter(function ($product) use ($product_ids) {
            return in_array($product->id, $product_ids);
        })->all();
    }

    /**
     * Get a product of a shop by shop domain and product ID.
     *
     * @param string $shop_domain
     * @param string $product_id
     * @return ProductCollection|null
     */
    public function getByShopDomainAndProductId(string $shop_domain, string $product_id): ?ProductCollection
    {
        $shop = ShopCollection::where('domain', $shop_domain)->first();
        if (!$shop) {
            return null;
        }
        return $shop->products()->where('id', $product_id)->first();
    }

    /**
     * Get list of referenced products for recommendation.
     *
     * @param ShopCollection $shop
     * @param string $product_id
     * @return array
     */
    public function getReferencedProducts(ShopCollection $shop, string $product_id): array
    {
        return $this->getProductRecommendationIds($shop, $product_id, 'referencedIds');
    }

    /**
     * Get list of product IDs using a specific attribute for recommendation.
     *
     * @param ShopCollection $shop
     * @param string $product_id
     * @param string $attribute
     * @return array
     */
    private function getProductRecommendationIds(ShopCollection $shop, string $product_id, string $attribute): array
    {
        $product = $this->getByIdAndShopCollection($product_id, $shop);
        if (!$product) {
            return [];
        }
        return $product->getAttributeValue($attribute);
    }

    /**
     * Get a product of a shop by ID.
     *
     * @param ShopCollection $shop
     * @param string $product_id
     * @return ProductCollection|null
     */
    public function getByIdAndShopCollection(string $product_id, ShopCollection $shop): ?ProductCollection
    {
        return $shop->products()->where('id', $product_id)->first();
    }

    /**
     * Get list of optional products for recommendation.
     *
     * @param ShopCollection $shop
     * @param string $product_id
     * @return array
     */
    public function getOptionalProducts(ShopCollection $shop, string $product_id): array
    {
        return $this->getProductRecommendationIds($shop, $product_id, 'optionIds');
    }
}
