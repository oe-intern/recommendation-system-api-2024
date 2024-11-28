<?php

namespace App\Storage\Queries;

use App\Collections\ProductCollection;
use App\Contracts\Queries\IProductQuery;
use App\Exceptions\ProductNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductQuery implements IProductQuery
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
            ->where('shop_collection_domain', $shop_domain)
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
            ->where('shop_collection_domain', $shop_domain)
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
            ->where('shop_collection_domain', $shop_domain)
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
        return $this->getProductRecommendationIds($product_id, 'manualIds');
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

    /**
     * Validate product IDs exist in the shop.
     *
     * @param string $shop_domain
     * @param array $product_ids
     * @return void
     *
     * @throws ProductNotFoundException
     */
    public function validateProductIds(string $shop_domain, array $product_ids): void
    {
        $existing_product_ids = $this->getByShopDomainAndIds($shop_domain, $product_ids);

        $not_existing = array_diff($product_ids, $existing_product_ids);
        if (!empty($not_existing)) {
            throw new ProductNotFoundException($shop_domain, $not_existing);
        }
    }

	/**
	 * Validate product IDs exist in the shop.
	 *
	 * @param string $shop_domain
	 * @param string $product_id
	 * @return ProductCollection
	 *
	 * @throws ProductNotFoundException
	 */
	public function validateProductId(string $shop_domain, string $product_id): ProductCollection
	{
		$product = $this->getByShopDomainAndId($shop_domain, $product_id);

		if (!$product) {
			throw new ProductNotFoundException($shop_domain, $product_id);
		}

		return $product;
	}
}
