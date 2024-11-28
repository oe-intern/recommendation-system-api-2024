<?php

namespace App\Contracts\Queries;

use App\Collections\ProductCollection;
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
     * Get list products of a shop by IDs
     *
     * @param array $product_ids
     * @param string $shop_domain
     * @return array
     */
    public function getByShopDomainAndIds(string $shop_domain, array $product_ids): array;

    /**
     * Check if the products exist in the database.
     *
     * @param string $shop_domain
     * @param string $product_id
     * @return ProductCollection
     *
     * @throws ModelNotFoundException
     */
    public function checkProductExist(string $shop_domain, string $product_id): ProductCollection;

    /**
     * Get a product of a shop by ID and shop domain.
     *
     * @param string $shop_domain
     * @param string $product_id
     * @return ProductCollection|null
     */
    public function getByShopDomainAndId(string $shop_domain, string $product_id): ?ProductCollection;

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
    public function getOptionalProducts(string $product_id): array;

	/**
	 * Validate product IDs exist in the shop.
	 *
	 * @param string $shop_domain
	 * @param array $product_ids
	 * @return void
	 *
	 * @throws ProductNotFoundException
	 */
	public function validateProductIds(string $shop_domain, array $product_ids): void;

	/**
	 * Validate product ID exist in the shop.
	 *
	 * @param string $shop_domain
	 * @param string $product_id
	 * @return ProductCollection
	 *
	 * @throws ProductNotFoundException
	 */
	public function validateProductId(string $shop_domain, string $product_id): ProductCollection;
}
