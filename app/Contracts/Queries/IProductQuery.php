<?php

namespace App\Contracts\Queries;

use App\Collections\ProductCollection;
use App\Collections\ShopCollection;
use App\Exceptions\ProductNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface IProductQuery
{
    /**
     * Get a product of a shop by ID.
     *
     * @param string $product_id
     * @return ProductCollection|null
     */
    public function getById(string $product_id): ?ProductCollection;

    /**
     * Get a product of a shop by GID.
     *
     * @param string $gid
     * @return ProductCollection|null
     */
    public function getByGid(string $gid): ?ProductCollection;

    /**
     * Get list products of a shop by IDs
     *
     * @param array $product_ids
     * @param string $shop_id
     * @return array
     */
    public function getByShopIdAndIds(string $shop_id, array $product_ids): array;

    /**
     * Get list products of a shop by collection
     *
     * @param ShopCollection $shop
     * @return array
     */
    public function getByShopCollection(ShopCollection $shop): array;

    /**
     * Check if the products exist in the database.
     *
     * @param string $shop_id
     * @param string $product_id
     * @return ProductCollection
     *
     * @throws ModelNotFoundException
     */
    public function checkProductExist(string $shop_id, string $product_id): ProductCollection;

    /**
     * Get a product of a shop by ID and shop domain.
     *
     * @param string $shop_id
     * @param string $product_id
     * @return ProductCollection|null
     */
    public function getByShopIdAndId(string $shop_id, string $product_id): ?ProductCollection;

    /**
     * Get a product of a shop by Shopify ID.
     *
     * @param string $shop_id
     * @param string $gid
     * @return ProductCollection|null
     */
    public function getByShopIdAndGid(string $shop_id, string $gid): ?ProductCollection;

    /**
     * Get list products ID using this product for recommendation.
     *
     * @param string $product_id
     * @return array
     */
    public function getReferencedProducts(string $product_id): array;

    /**
     * Get list of optional products for recommendation.
     *
     * * @param string $product_id
     * * @return array
     */
    public function getManualProducts(string $product_id): array;

    /**
     * Get list of optional products for recommendation.
     *
     * @param string $product_id
     * @return array
     */
    public function getDefaultProducts(string $product_id): array;

    /**
     * Get list of optional products for recommendation.
     *
     * @param string $product_id
     * @return array
     */
    public function getAutoRecommendationProducts(string $product_id): array;

	/**
	 * Validate product IDs exist in the shop.
	 *
	 * @param string $shop_id
	 * @param array $product_ids
	 * @return array
	 *
	 * @throws ProductNotFoundException
	 */
	public function validateProductIds(string $shop_id, array $product_ids): array;

    /**
     * Validate product IDs exist in the shop.
     *
     * @param string $shop_id
     * @param array $list_product_gid
     * @return array
     *
     * @throws ProductNotFoundException
     */
    public function validateListProductGid(string $shop_id, array $list_product_gid): array;

	/**
	 * Validate product ID exist in the shop.
	 *
	 * @param string $shop_id
	 * @param string $product_id
	 * @return ProductCollection
	 *
	 * @throws ProductNotFoundException
	 */
	public function validateProductId(string $shop_id, string $product_id): ProductCollection;

    /**
     * Get list of product GID by IDs.
     *
     * @param array $product_ids
     * @return array
     */
    public function getListGidByIds(array $product_ids): array;

    /**
     * Get list of product ID by GIDs.
     *
     * @param array $product_gids
     * @return array
     */
    public function getIdsByGids(array $product_gids): array;

    /**
     * Get product GID by ID.
     *
     * @param string $product_id
     * @return string
     */
    public function getGidById(string $product_id): string;

    /**
     * Get all product of a shop not in a list of product ids.
     *
     * @param string $shop_id
     * @param array $product_ids
     * @return array
     */
    public function getProductsNotIn(string $shop_id, array $product_ids): array;

    /**
     * Get all product of a shop not in a list of product GIDs.
     *
     * @param string $shop_id
     * @param array $product_gids
     * @return array
     */
    public function getProductGidsNotIn(string $shop_id, array $product_gids): array;

    /**
     * Get list of product active by IDs.
     *
     * @param array $ids
     * @return array
     */
    public function getActiveProducts(array $ids): array;

    /**
     * Get handle and GID by IDs.
     *
     * @param array $product_ids
     * @return array
     */
    public function getHandleAndGidByIds(array $product_ids): array;

    /**
     * Get existing products by GIDs and shop ID.
     *
     * @param string $shop_id
     * @param array $products_gid
     * @return array
     */
    public function getExistingProductsGid(string $shop_id, array $products_gid): array;

    /**
     * Get product ID and GID by shop ID.
     *
     * @param string $shop_id
     * @return array
     */
    public function getIdAndGidByShopId(string $shop_id): array;

    /**
     * Get map of product ID with key GID by shop ID.
     *
     * @param string $shop_id
     * @return array
     */
    public function getMapIdWithKeyGidByShopId(string $shop_id): array;
}
