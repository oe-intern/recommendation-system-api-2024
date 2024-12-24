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
     * @param string $productId
     * @return ProductCollection|null
     */
    public function getById(string $productId): ?ProductCollection
    {
        return ProductCollection::query()->find($productId);
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
     * @param string $shopId
     * @param string $productId
     * @return ProductCollection
     *
     * @throws ModelNotFoundException
     */
    public function checkProductExist(string $shopId, string $productId): ProductCollection
    {
        return ProductCollection::query()
            ->where('id', $productId)
            ->where('shop_id', $shopId)
            ->firstOrFail();
    }

    /**
     * Get list of referenced products for recommendation.
     *
     * @param string $productId
     * @return array
     */
    public function getReferencedProducts(string $productId): array
    {
        return $this->getProductRecommendationIds($productId, 'referenced_ids');
    }

    /**
     * Get list of product IDs using a specific attribute for recommendation.
     *
     * @param string $productId
     * @param string $attribute
     * @return array
     */
    private function getProductRecommendationIds(string $productId, string $attribute): array
    {
        return ProductCollection::query()
            ->where('id', $productId)
            ->first()
            ->getAttributeValue($attribute);
    }

    /**
     * Get list of optional products for recommendation.
     *
     * @param string $productId
     * @return array
     */
    public function getManualProducts(string $productId): array
    {
        return $this->getProductRecommendationIds($productId, 'manual_ids');
    }

    /**
     * Get list of optional products for recommendation.
     *
     * @param string $productId
     * @return array
     */
    public function getDefaultProducts(string $productId): array
    {
        return $this->getProductRecommendationIds($productId, 'manual_ids');
    }

    /**
     * Get list of optional products for recommendation.
     *
     * @param string $productId
     * @return array
     */
    public function getAutoRecommendationProducts(string $productId): array
    {
        return $this->getProductRecommendationIds($productId, 'recommendation_ids');
    }

    /**
     * Validate product IDs exist in the shop.
     *
     * @param string $shopId
     * @param array $productIds
     * @return array
     *
     * @throws ProductNotFoundException
     */
    public function validateProductIds(string $shopId, array $productIds): array
    {
        $existingProducts = $this->getByShopIdAndIds($shopId, $productIds);
        $existing_product_ids = collect($existingProducts)->pluck('id')->toArray();

        $not_existing = array_diff($productIds, $existing_product_ids);
        if (!empty($not_existing)) {
            throw new ProductNotFoundException($not_existing);
        }

        return $existingProducts;
    }

    /**
     * Validate product IDs exist in the shop.
     *
     * @param string $shopId
     * @param array $productGids
     * @return array
     *
     * @throws ProductNotFoundException
     */
    public function validateListProductGid(string $shopId, array $productGids): array
    {
        $existingProducts = $this->getByShopIdAndListGid($shopId, $productGids);
        $existing_product_ids = collect($existingProducts)->pluck('gid')->toArray();

        $not_existing = array_diff($productGids, $existing_product_ids);
        if (!empty($not_existing)) {
            throw new ProductNotFoundException($not_existing);
        }

        return collect($existingProducts)->pluck('id')->toArray();
    }

    /**
     * Get list products of a shop by IDs
     *
     * @param array $productIds
     * @param string $shopId
     * @return array
     */
    public function getByShopIdAndIds(string $shopId, array $productIds): array
    {
        return ProductCollection::query()
            ->where('shop_id', $shopId)
            ->whereIn('id', $productIds)
            ->get()
            ->all();
    }

    /**
     * Get list products of a shop by list Gid
     *
     * @param array $productGids
     * @param string $shopId
     * @return array
     */
    public function getByShopIdAndListGid(string $shopId, array $productGids): array
    {
        return ProductCollection::query()
            ->where('shop_id', $shopId)
            ->whereIn('gid', $productGids)
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
     * @param string $shopId
     * @param string $productId
     * @return ProductCollection
     *
     * @throws ProductNotFoundException
     */
    public function validateProductId(string $shopId, string $productId): ProductCollection
    {
        $product = $this->getByShopIdAndId($shopId, $productId);

        if (!$product) {
            throw new ProductNotFoundException($productId);
        }

        return $product;
    }

    /**
     * Get a product of a shop by shop domain and product ID.
     *
     * @param string $shopId
     * @param string $productId
     * @return ProductCollection|null
     */
    public function getByShopIdAndId(string $shopId, string $productId): ?ProductCollection
    {
        return ProductCollection::query()
            ->where('shop_id', $shopId)
            ->where('id', $productId)
            ->first();
    }

    /**
     * Get a product of a shop by Shopify ID.
     *
     * @param string $shopId
     * @param string $gid
     * @return ProductCollection|null
     */
    public function getByShopIdAndGid(string $shopId, string $gid): ?ProductCollection
    {
        return ProductCollection::query()
            ->where('shop_id', $shopId)
            ->where('gid', $gid)
            ->first();
    }

    /**
     * Get list of product GID by IDs.
     *
     * @param array $productIds
     * @return array
     */
    public function getListGidByIds(array $productIds): array
    {
        return ProductCollection::query()
            ->whereIn('id', $productIds)
            ->get()
            ->pluck('gid')
            ->toArray();
    }

    /**
     * Get list of product ID by GIDs.
     *
     * @param array $productGids
     * @return array
     */
    public function getIdsByGids(array $productGids): array
    {
        return ProductCollection::query()
            ->whereIn('gid', $productGids)
            ->get()
            ->pluck('id')
            ->toArray();
    }

    /**
     * Get product GID by ID.
     *
     * @param string $productId
     * @return string
     */
    public function getGidById(string $productId): string
    {
        return ProductCollection::query()
            ->where('id', $productId)
            ->first()
            ->getGid();
    }

    /**
     * Get all product of a shop not in a list of product ids.
     *
     * @param string $shopId
     * @param array $productIds
     * @return array
     */
    public function getProductsNotIn(string $shopId, array $productIds): array
    {
        return ProductCollection::query()
            ->where('shop_id', $shopId)
            ->whereNotIn('id', $productIds)
            ->get()
            ->pluck('id')
            ->toArray();
    }

    /**
     * Get all product of a shop not in a list of product GIDs.
     *
     * @param string $shopId
     * @param array $productGids
     * @return array
     */
    public function getProductGidsNotIn(string $shopId, array $productGids): array
    {
        return ProductCollection::query()
            ->where('shop_id', $shopId)
            ->whereNotIn('gid', $productGids)
            ->get()
            ->pluck('gid')
            ->toArray();
    }

    /**
     * Get list of product active by IDs.
     *
     * @param array $ids
     * @return array
     */
    public function getActiveProducts(array $ids): array
    {
        return ProductCollection::query()
            ->whereIn('id', $ids)
            ->whereIn('status', ['active', 'ACTIVE'])
            ->get()
            ->pluck('id')
            ->toArray();
    }

    /**
     * Get list of product handle and GID by IDs.
     *
     * @param array $productIds
     * @return array
     */
    public function getHandleAndGidByIds(array $productIds): array
    {
        return ProductCollection::query()
            ->whereIn('id', $productIds)
            ->get()
            ->map(function ($product) {
                return [
                    'handle' => $product->handle,
                    'id' => $product->gid,
                ];
            })
            ->toArray();
    }

    /**
     * Get existing products by GIDs and shop ID.
     *
     * @param string $shopId
     * @param array $productGids
     * @return array
     */
    public function getExistingProductsGid(string $shopId, array $productGids): array
    {
        return ProductCollection::query()
            ->where('shop_id', $shopId)
            ->whereIn('gid', $productGids)
            ->get()
            ->pluck('gid')
            ->toArray();
    }

    /**
     * Get product ID and GID by shop ID.
     *
     * @param string $shopId
     * @return array
     */
    public function getIdAndGidByShopId(string $shopId): array
    {
        return ProductCollection::query()
            ->where('shop_id', $shopId)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'gid' => $product->gid,
                ];
            })
            ->toArray();
    }

    /**
     * Get map of product ID with key GID by shop ID.
     *
     * @param string $shopId
     * @return array
     */
    public function getMapIdWithKeyGidByShopId(string $shopId): array
    {
        return ProductCollection::query()
            ->where('shop_id', $shopId)
            ->get()
            ->pluck('id', 'gid')
            ->toArray();
    }
}
