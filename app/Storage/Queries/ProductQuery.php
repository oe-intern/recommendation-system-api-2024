<?php

namespace App\Storage\Queries;

use App\Collections\ProductCollection;
use App\Collections\ShopCollection;
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
     * Get a product of a shop by GID.
     *
     * @param string $gid
     * @return ProductCollection|null
     */
    public function getByGid(string $gid): ?ProductCollection
    {
        return ProductCollection::query()->where('gid', $gid)->first();
    }

    /**
     * Check if the product exist in the database.
     *
     * @param string $shop_id
     * @param string $product_id
     * @return ProductCollection
     *
     * @throws ModelNotFoundException
     */
    public function checkProductExist(string $shop_id, string $product_id): ProductCollection
    {
        return ProductCollection::query()
            ->where('id', $product_id)
            ->where('shop_id', $shop_id)
            ->firstOrFail();
    }

    /**
     * Get list of referenced products for recommendation.
     *
     * @param string $product_id
     * @return array
     */
    public function getReferencedProducts(string $product_id): array
    {
        return $this->getProductRecommendationIds($product_id, 'referenced_ids');
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
     * Get list of optional products for recommendation.
     *
     * @param string $product_id
     * @return array
     */
    public function getManualProducts(string $product_id): array
    {
        return $this->getProductRecommendationIds($product_id, 'manual_ids');
    }

    /**
     * Validate product IDs exist in the shop.
     *
     * @param string $shop_id
     * @param array $product_ids
     * @return array
     *
     * @throws ProductNotFoundException
     */
    public function validateProductIds(string $shop_id, array $product_ids): array
    {
        $existing_products = $this->getByShopIdAndIds($shop_id, $product_ids);
        $existing_product_ids = collect($existing_products)->pluck('id')->toArray();

        $not_existing = array_diff($product_ids, $existing_product_ids);
        if (!empty($not_existing)) {
            throw new ProductNotFoundException($not_existing);
        }

        return $existing_products;
    }

    /**
     * Validate product IDs exist in the shop.
     *
     * @param string $shop_id
     * @param array $list_product_gid
     * @return array
     *
     * @throws ProductNotFoundException
     */
    public function validateListProductGid(string $shop_id, array $list_product_gid): array
    {
        $existing_products = $this->getByShopIdAndListGid($shop_id, $list_product_gid);
        $existing_product_ids = collect($existing_products)->pluck('gid')->toArray();

        $not_existing = array_diff($list_product_gid, $existing_product_ids);
        if (!empty($not_existing)) {
            throw new ProductNotFoundException($not_existing);
        }

        return collect($existing_products)->pluck('id')->toArray();
    }

    /**
     * Get list products of a shop by IDs
     *
     * @param array $product_ids
     * @param string $shop_id
     * @return array
     */
    public function getByShopIdAndIds(string $shop_id, array $product_ids): array
    {
        return ProductCollection::query()
            ->where('shop_id', $shop_id)
            ->whereIn('id', $product_ids)
            ->get()
            ->all();
    }

    /**
     * Get list products of a shop by list Gid
     *
     * @param array $list_product_gid
     * @param string $shop_id
     * @return array
     */
    public function getByShopIdAndListGid(string $shop_id, array $list_product_gid): array
    {
        return ProductCollection::query()
            ->where('shop_id', $shop_id)
            ->whereIn('gid', $list_product_gid)
            ->get()
            ->all();
    }

    /**
     * Get list products of a shop by collection
     *
     * @param ShopCollection $shop
     * @return array
     */
    public function getByShopCollection(ShopCollection $shop): array
    {
        return $shop->products()->get()->all();
    }

    /**
     * Validate product IDs exist in the shop.
     *
     * @param string $shop_id
     * @param string $product_id
     * @return ProductCollection
     *
     * @throws ProductNotFoundException
     */
    public function validateProductId(string $shop_id, string $product_id): ProductCollection
    {
        $product = $this->getByShopIdAndId($shop_id, $product_id);

        if (!$product) {
            throw new ProductNotFoundException($product_id);
        }

        return $product;
    }

    /**
     * Get a product of a shop by shop domain and product ID.
     *
     * @param string $shop_id
     * @param string $product_id
     * @return ProductCollection|null
     */
    public function getByShopIdAndId(string $shop_id, string $product_id): ?ProductCollection
    {
        return ProductCollection::query()
            ->where('shop_id', $shop_id)
            ->where('id', $product_id)
            ->first();
    }

    /**
     * Get a product of a shop by Shopify ID.
     *
     * @param string $shop_id
     * @param string $gid
     * @return ProductCollection|null
     */
    public function getByShopIdAndGid(string $shop_id, string $gid): ?ProductCollection
    {
        return ProductCollection::query()
            ->where('shop_id', $shop_id)
            ->where('gid', $gid)
            ->first();
    }

    /**
     * Get list of product GID by IDs.
     *
     * @param array $product_ids
     * @return array
     */
    public function getListGidByIds(array $product_ids): array
    {
        return ProductCollection::query()
            ->whereIn('id', $product_ids)
            ->get()
            ->pluck('gid')
            ->toArray();
    }

    /**
     * Get product GID by ID.
     *
     * @param string $product_id
     * @return string
     */
    public function getGidById(string $product_id): string
    {
        return ProductCollection::query()
            ->where('id', $product_id)
            ->first()
            ->getGid();
    }

    /**
     * Get all product of a shop not in a list of product ids.
     *
     * @param string $shop_id
     * @param array $product_ids
     * @return array
     */
    public function getProductsNotIn(string $shop_id, array $product_ids): array
    {
        return ProductCollection::query()
            ->where('shop_id', $shop_id)
            ->whereNotIn('id', $product_ids)
            ->get()
            ->pluck('id')
            ->toArray();
    }
}
