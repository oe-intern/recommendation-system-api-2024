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
    protected IProductQuery $product_query;

    /**
     * ProductCommand constructor.
     *
     * @param IProductQuery $product_query
     */
    public function __construct(
        IProductQuery $product_query,
    ) {
        $this->product_query = $product_query;
    }

    /**
     * Create a product.
     *
     * @param ShopCollection $shop
     * @param array $product
     * @return void
     */
    public function create(ShopCollection $shop, array $product): void
    {
        $shop->products()->create($product);
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
        $chunks = array_chunk($products, 100);
        foreach ($chunks as $chunk) {
            $shop->products()->createMany($chunk);
        }
    }

    /**
     * Update the recommendation type a product.
     *
     * @param ProductCollection $product
     * @param RecommendationType $recommendation_type
     * @return bool
     */
    public function setRecommendationType(
        ProductCollection $product,
        RecommendationType $recommendation_type
    ): bool {
        return $product->update([
            'recommendation_type' => $recommendation_type,
        ]);
    }

    /**
     * Update a product of a shop with data from Shopify.
     *
     * @param ProductCollection $product
     * @param array $product_data
     * @return bool
     */
    public function update(ProductCollection $product, array $product_data): bool
    {
        return $product->update($product_data);
    }

    /**
     * Update a list products of a shop with data from Shopify.
     *
     * @param array $product_data
     * @return bool
     */
    public function updateManyByGid(array $product_data): bool
    {
        return ProductCollection::query()->upsert($product_data, ['gid'], ['status', 'type', 'handle']);
    }

    /**
     * Update list recommendation for products.
     *
     * @param array $recommendation_data
     * @return bool
     */
    public function updateManyRecommendation(array $recommendation_data): bool
    {
        return $this->updateRecommendation($recommendation_data, 'recommendation_ids');
    }

    /**
     * Update list recommendation for products.
     *
     * @param array $recommendation_data
     * @return bool
     */
    public function updateManyDefaultRecommendation(array $recommendation_data): bool
    {
        return $this->updateRecommendation($recommendation_data, 'default_recommendation_ids');
    }

    /**
     * Update product recommendation with attribute.
     *
     * @param array $recommendation_data
     * @param string $attribute
     * @return bool
     */
    private function updateRecommendation(array $recommendation_data, string $attribute): bool
    {
        $collection = DB::connection('mongodb')->getCollection('products');
        $operations = [];
        foreach ($recommendation_data as $product_id => $recommendation_ids) {
            $operations[] = [
                'updateOne' => [
                    ['_id' => new ObjectId($product_id)],
                    ['$set' => [$attribute => $recommendation_ids]],
                ],
            ];
        }

        $result = $collection->bulkWrite($operations);
        return $result->getModifiedCount() > 0;
    }

    /**
     * Customize the recommendation products for each product.
     *
     * @param ProductCollection $product
     * @param string $shop_id
     * @param array $recommendations
     * @param RecommendationType $recommendation_type
     * @return bool
     */
    public function setManualProduct(
        ProductCollection $product,
        string $shop_id,
        array $recommendations,
        RecommendationType $recommendation_type,
    ): bool {
        $product_id = $product->getId();
        $removed_recommendations = array_diff($product->getAttributeValue('manual_ids'), $recommendations);

        $product->update([
            'manual_ids' => $recommendations,
            'recommendation_type' => $recommendation_type,
        ]);

        foreach ($recommendations as $recommended_product_id) {
            $this->addReferenceProduct($recommended_product_id, $product_id);
        }

        foreach ($removed_recommendations as $removed_recommendation) {
            $this->removeProductRecommendation($product_id, $removed_recommendation);
        }

        return true;
    }

    /**
     * Add a reference this product using product for recommendation.
     *
     * @param string $product_id
     * @param string $reference_product_id
     * @return void
     */
    private function addReferenceProduct(string $product_id, string $reference_product_id): void
    {
        $product = $this->product_query->getById($product_id);

        $product?->update([
            'referenced_ids' => array_unique(array_merge(
                $product->getAttributeValue('referenced_ids'),
                [$reference_product_id])
            ),
        ]);
    }

    /**
     * Remove a product recommended for this product.
     *
     * @param string $product_id
     * @param string $recommended_product_id
     * @return void
     */
    public function removeProductRecommendation(
        string $product_id,
        string $recommended_product_id
    ): void {
        $product = $this->product_query->getById($product_id);
        $product?->update([
            'manual_ids' => array_diff($product->getAttributeValue('manual_ids'), [$recommended_product_id]),
        ]);

        $this->removeReferenceProduct($recommended_product_id, $product_id);
    }

    /**
     * Remove a reference this product using product for recommendation.
     *
     * @param string $product_id
     * @param string $reference_product_id
     * @return void
     */
    private function removeReferenceProduct(
        string $product_id,
        string $reference_product_id
    ): void {
        $product = $this->product_query->getById($product_id);
        $product?->update([
            'referenced_ids' => array_diff($product->getAttributeValue('referenced_ids'), [$reference_product_id]),
        ]);
    }

    /**
     * Add a product recommended for this product.
     *
     * @param string $product_id
     * @param string $recommended_product_id
     * @return void
     */
    public function addRecommendation(string $product_id, string $recommended_product_id): void
    {
        $product = $this->product_query->getById($product_id);
        $product?->update([
            'manual_ids' => array_unique(array_merge($product->getAttributeValue('manual_ids'),
                [$recommended_product_id])),
        ]);

        $this->addReferenceProduct($recommended_product_id, $product_id);
    }

    /**
     * Delete a product of a shop.
     *
     * @param ProductCollection $product
     * @return bool
     */
    public function delete(ProductCollection $product): bool
    {
        $this->removeRelationshipRecommendation($product);

        return $product->delete();
    }

    /**
     * Remove a relationship of a product with another product.
     *
     * @param ProductCollection $product
     * @return void
     */
    private function removeRelationshipRecommendation(ProductCollection $product): void
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
        ProductCollection $product
    ): void {
        $product_id = $product->getId();
        $product_recommendation_ids = $product->getManualIds();

        foreach ($product_recommendation_ids as $product_recommendation_id) {
            $this->removeReferenceProduct($product_recommendation_id, $product_id);
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
        $product_id = $product->getId();
        $product_referenced_ids = $product->getAttributeValue('referenced_ids');

        foreach ($product_referenced_ids as $product_referenced_id) {
            $this->removeProductRecommendation($product_referenced_id, $product_id);
        }
    }

    /**
     * Active the recommendation for all products of a shop.
     *
     * @param string $shop_id
     * @return bool
     */
    public function activateRecommendation(string $shop_id): bool
    {
        return ProductCollection::query()
            ->where('shop_id', $shop_id)
            ->where('recommendation_type', RecommendationType::DEFAULT)
            ->update([
                'recommendation_type' => RecommendationType::AUTO,
            ]);
    }

    /**
     * Deactivate the recommendation for all products of a shop.
     *
     * @param string $shop_id
     * @return bool
     */
    public function deactivateRecommendation(string $shop_id): bool
    {
        return ProductCollection::query()
            ->where('shop_id', $shop_id)
            ->where('recommendation_type', RecommendationType::AUTO)
            ->update([
                'recommendation_type' => RecommendationType::DEFAULT,
            ]);
    }
}
