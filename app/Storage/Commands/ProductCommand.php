<?php

namespace App\Storage\Commands;

use App\Collections\ProductCollection;
use App\Collections\ShopCollection;
use App\Contracts\Commands\IProductCommand;
use App\Contracts\Queries\IProductQuery;
use App\Objects\Enums\RecommendationType;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\ObjectId;

class ProductCommand implements IProductCommand
{
    /**
     * @var IProductQuery
     */
    protected IProductQuery $productQuery;

    /**
     * ProductCommand constructor.
     *
     * @param IProductQuery $productQuery
     */
    public function __construct(
        IProductQuery $productQuery,
    ) {
        $this->productQuery = $productQuery;
    }

    /**
     * Create a product.
     *
     * @param ShopCollection $shop
     * @param array $product
     * @return ProductCollection
     */
    public function create(ShopCollection $shop, array $product): ProductCollection
    {
        $product['shop_id'] = $shop->getId();
        return ProductCollection::query()->create($product);
    }

    /**
     * Create a list products of a shop with data from Shopify.
     *
     * @param ShopCollection $shop
     * @param array $products
     * @return void
     */
    public function createMany(ShopCollection $shop, array $products): void
    {
        array_map(fn($chunk) => $shop->products()->createMany($chunk), array_chunk($products, 100));
    }

    /**
     * Update the recommendation type a product.
     *
     * @param ProductCollection $product
     * @param RecommendationType $recommendationType
     * @return bool
     */
    public function setRecommendationType(
        ProductCollection $product,
        RecommendationType $recommendationType,
    ): bool {
        return $product->update([
            'recommendation_type' => $recommendationType,
        ]);
    }

    /**
     * Update a product of a shop with data from Shopify.
     *
     * @param ProductCollection $product
     * @param array $productData
     * @return bool
     */
    public function update(ProductCollection $product, array $productData): bool
    {
        return $product->update($productData);
    }

    /**
     * Update a list products of a shop with data from Shopify.
     *
     * @param array $productData
     * @return bool
     */
    public function updateManyByGid(array $productData): bool
    {
        return ProductCollection::query()->upsert($productData, ['gid'], ['status', 'type', 'handle']);
    }

    /**
     * Update recommendation for a product.
     *
     * @param string $productId
     * @param array $recommendations
     * @return bool
     */
    public function updateProductRecommendation(string $productId, array $recommendations): bool
    {
        return ProductCollection::query()
            ->where('_id', $productId)
            ->update([
                'recommendation_ids' => $recommendations,
            ]);
    }

    /**
     * Update recommendation for a new product.
     *
     * @param string $productId
     * @param array $recommendations
     * @param RecommendationType $recommendationType
     * @return bool
     */
    public function updateNewProductRecommendation(
        string $productId,
        array $recommendations,
        RecommendationType $recommendationType,
    ): bool {
        return ProductCollection::query()
            ->where('_id', $productId)
            ->update([
                'default_recommendation_ids' => $recommendations,
                'recommendation_ids' => $recommendations,
                'recommendation_type' => $recommendationType,
            ]);
    }

    /**
     * Update list recommendation for products.
     *
     * @param array $recommendationData
     * @return bool
     */
    public function updateManyRecommendation(array $recommendationData): bool
    {
        return $this->updateRecommendation($recommendationData, 'recommendation_ids');
    }

    /**
     * Update product recommendation with attribute.
     *
     * @param array $recommendationData
     * @param string $attribute
     * @return bool
     */
    private function updateRecommendation(array $recommendationData, string $attribute): bool
    {
        $operations = array_map(fn($productId, $recommendationIds)
            => [
            'updateOne' => [
                ['_id' => new ObjectId($productId)],
                ['$set' => [$attribute => $recommendationIds]],
            ],
        ], array_keys($recommendationData), array_values($recommendationData));

        if (empty($operations)) return true;

        $result = DB::connection('mongodb')->getCollection('products')->bulkWrite($operations);
        return $result->getModifiedCount() > 0;
    }

    /**
     * Update list recommendation for products.
     *
     * @param array $recommendationData
     * @return bool
     */
    public function updateManyDefaultRecommendation(array $recommendationData): bool
    {
        return $this->updateRecommendation($recommendationData, 'default_recommendation_ids');
    }

    /**
     * Customize the recommendation products for each product.
     *
     * @param ProductCollection $product
     * @param string $shopId
     * @param array $recommendations
     * @param RecommendationType $recommendationType
     * @return bool
     */
    public function setManualProduct(
        ProductCollection $product,
        string $shopId,
        array $recommendations,
        RecommendationType $recommendationType,
    ): bool {
        $productId = $product->getId();
        $removedRecommendations = array_diff($product->getAttributeValue('manual_ids'), $recommendations);

        $product->update([
            'manual_ids' => $recommendations,
            'recommendation_type' => $recommendationType,
        ]);

        $this->updateReferenceProduct($productId, $recommendations, $removedRecommendations);
        return true;
    }

    private function updateReferenceProduct(string $productId, array $referenceProductIds, array $removeReferenceProductIds): void
    {
        array_map(fn($recommendedProductId) => $this->addReferenceProduct($recommendedProductId, $productId), $referenceProductIds);
        array_map(fn($removedRecommendation) => $this->removeProductRecommendation($productId, $removedRecommendation), $removeReferenceProductIds);
    }

    /**
     * Add a reference this product using product for recommendation.
     *
     * @param string $productId
     * @param string $referenceProductId
     * @return void
     */
    private function addReferenceProduct(string $productId, string $referenceProductId): void
    {
        $product = $this->productQuery->getById($productId);

        $product?->update([
            'referenced_ids' => array_unique(array_merge(
                $product->getAttributeValue('referenced_ids'),
                [$referenceProductId]),
            ),
        ]);
    }

    /**
     * Remove a product recommended for this product.
     *
     * @param string $productId
     * @param string $recommendedProductId
     * @return void
     */
    public function removeProductRecommendation(
        string $productId,
        string $recommendedProductId,
    ): void {
        $product = $this->productQuery->getById($productId);
        $product?->update([
            'manual_ids' => array_diff($product->getAttributeValue('manual_ids'), [$recommendedProductId]),
        ]);

        $this->removeReferenceProduct($recommendedProductId, $productId);
    }

    /**
     * Remove a reference this product using product for recommendation.
     *
     * @param string $productId
     * @param string $referenceProductId
     * @return void
     */
    private function removeReferenceProduct(
        string $productId,
        string $referenceProductId,
    ): void {
        $product = $this->productQuery->getById($productId);
        $product?->update([
            'referenced_ids' => array_diff($product->getAttributeValue('referenced_ids'), [$referenceProductId]),
        ]);
    }

    /**
     * Add a product recommended for this product.
     *
     * @param string $productId
     * @param string $recommendedProductId
     * @return void
     */
    public function addRecommendation(string $productId, string $recommendedProductId): void
    {
        $product = $this->productQuery->getById($productId);
        $product?->update([
            'manual_ids' => array_unique(array_merge($product->getAttributeValue('manual_ids'),
                [$recommendedProductId])),
        ]);

        $this->addReferenceProduct($recommendedProductId, $productId);
    }

    /**
     * Delete a list of products by gid.
     *
     * @param array $productGids
     * @return bool
     */
    public function deleteManyByGid(array $productGids): bool
    {
        return ProductCollection::query()
            ->whereIn('gid', $productGids)
            ->delete();
    }

    /**
     * Delete a product of a shop.
     *
     * @param ProductCollection $product
     * @return bool
     */
    public function delete(ProductCollection $product): bool
    {
        $this->clearRelationshipRecommendation($product);

        return $product->delete();
    }

    /**
     * Remove a relationship of a product with another product.
     *
     * @param ProductCollection $product
     * @return void
     */
    private function clearRelationshipRecommendation(ProductCollection $product): void
    {
        $this->removeRelationshipRecommendationProduct($product);

        $this->removeRelationshipReferencedProduct($product);
    }

    /**
     * Remove reference of this product to another product.
     *
     * @param ProductCollection $product
     * @return void
     */
    private function removeRelationshipRecommendationProduct(
        ProductCollection $product,
    ): void {
        $productId = $product->getId();
        $productRecommendationIds = $product->getManualIds();

        foreach ($productRecommendationIds as $productRecommendationId) {
            $this->removeReferenceProduct($productRecommendationId, $productId);
        }
    }

    /**
     * Remove recommended product for this product from another product.
     *
     * @param ProductCollection $product
     * @return void
     */
    private function removeRelationshipReferencedProduct(
        ProductCollection $product,
    ): void {
        $productId = $product->getId();
        $productReferencedIds = $product->getAttributeValue('referenced_ids');

        foreach ($productReferencedIds as $productReferencedId) {
            $this->removeProductRecommendation($productReferencedId, $productId);
        }
    }

    /**
     * Active the recommendation for all products of a shop.
     *
     * @param string $shopId
     * @return bool
     */
    public function activateRecommendation(string $shopId): bool
    {
        return ProductCollection::query()
            ->where('shop_id', $shopId)
            ->where('recommendation_type', RecommendationType::DEFAULT)
            ->update([
                'recommendation_type' => RecommendationType::AUTO,
            ]);
    }

    /**
     * Deactivate the recommendation for all products of a shop.
     *
     * @param string $shopId
     * @return bool
     */
    public function deactivateRecommendation(string $shopId): bool
    {
        return ProductCollection::query()
            ->where('shop_id', $shopId)
            ->where('recommendation_type', RecommendationType::AUTO)
            ->update([
                'recommendation_type' => RecommendationType::DEFAULT,
            ]);
    }
}
