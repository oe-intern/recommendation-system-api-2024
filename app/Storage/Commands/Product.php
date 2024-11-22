<?php

namespace App\Storage\Commands;

use App\Collections\Product as ProductCollection;
use App\Collections\Shop as ShopCollection;
use App\Contracts\Commands\Product as ProductCommand;
use App\Contracts\Commands\RelationshipScore as RelationshipScoreCommand;
use App\Contracts\Queries\Product as ProductQuery;
use App\Contracts\Queries\RelationshipScore as RelationshipScoreQuery;
use App\Objects\Enums\RecommendationType;
use App\Objects\Enums\RecommendationType as RecommendationTypeEnum;

class Product implements ProductCommand
{
    /**
     * @var ProductQuery
     */
    protected ProductQuery $product_query;

    /**
     * @var RelationshipScoreQuery
     */
    protected RelationshipScoreQuery $relationship_score_query;

    /**
     * @var RelationshipScoreCommand
     */
    protected RelationshipScoreCommand $relationship_score_command;

    /**
     * Product constructor.
     *
     * @param ProductQuery $product_query
     * @param RelationshipScoreQuery $relationship_score_query
     * @param RelationshipScoreCommand $relationship_score_command
     */
    public function __construct(
        ProductQuery $product_query,
        RelationshipScoreQuery $relationship_score_query,
        RelationshipScoreCommand $relationship_score_command
    ) {
        $this->product_query = $product_query;
        $this->relationship_score_query = $relationship_score_query;
        $this->relationship_score_command = $relationship_score_command;
    }

    /**
     * Create a product.
     *
     * @param array $product
     * @param ShopCollection $shop
     * @return void
     */
    public function create(array $product, ShopCollection $shop): void
    {
        $shop->products()->create($product);
    }

    /**
     * Create a list products of a shop with data from Shopify.
     *
     * @param array $products
     * @param ShopCollection $shop
     * @return void
     */
    public function createMany(array $products, ShopCollection $shop): void
    {
        $shop->products()->createMany($products);
    }

    /**
     * Update the recommendation type a product.
     *
     * @param ProductCollection $product
     * @param RecommendationTypeEnum $recommendation_type
     * @return bool
     */
    public function setRecommendationType(
        ProductCollection $product,
        RecommendationTypeEnum $recommendation_type
    ): bool {
        return $product->update([
            'recommendationType' => $recommendation_type,
        ]);
    }

    /**
     * Update a product of a shop with data from Shopify.
     *
     * @param array $product
     * @param ShopCollection $shop
     * @return bool
     */
    public function update(array $product, ShopCollection $shop): bool
    {
        $product_collection = $shop->products()->where('id', $product['id'])->first();
        if (!$product_collection) {
            return false;
        }

        return $product_collection->update($product);
    }

    /**
     * Customize the recommendation products for each product.
     *
     * @param ProductCollection $product
     * @param ShopCollection $shop
     * @param RecommendationType $recommendation_type
     * @param array $recommendations
     * @return bool
     */
    public function setRecommendationProduct(
        ProductCollection $product,
        ShopCollection $shop,
        RecommendationType $recommendation_type,
        array $recommendations
    ): bool {
        $recommendations = $this->getValidRecommendationProducts($shop, $product->getAttributeValue('id'),
            $recommendations);

        $product->update([
            'recommendationType' => $recommendation_type,
            'optionIds' => $recommendations,
        ]);

        foreach ($recommendations as $recommended_product_id) {
            $this->addReferenceProduct($recommended_product_id, $shop, $product->getAttributeValue('id'));
        }
        return true;
    }

    /**
     * Get list of products valid for recommendation.
     *
     * @param ShopCollection $shop
     * @param string $product_id
     * @param array $product_ids
     * @return array
     */
    public function getValidRecommendationProducts(ShopCollection $shop, string $product_id, array $product_ids): array
    {
        $filtered_ids = array_unique(array_filter($product_ids, fn($id) => (string) $id !== $product_id));

        $recommended_products = $this->product_query->getByIdsAndShopCollection($filtered_ids, $shop);

        return collect($recommended_products)->pluck('id')->toArray();
    }

    /**
     * Add a reference this product using product for recommendation.
     *
     * @param string $product_id
     * @param ShopCollection $shop
     * @param string $reference_product_id
     * @return void
     */
    private function addReferenceProduct(string $product_id, ShopCollection $shop, string $reference_product_id): void
    {
        $product = $this->product_query->getByIdAndShopCollection($product_id, $shop);
        $product?->update([
            'referencedIds' => array_unique(array_merge($product->getAttributeValue('referencedIds'),
                [$reference_product_id])),
        ]);
    }

    /**
     * Add a product recommended for this product.
     *
     * @param string $product_id
     * @param ShopCollection $shop
     * @param string $recommended_product_id
     * @return void
     */
    public function addRecommendation(string $product_id, ShopCollection $shop, string $recommended_product_id): void
    {
        $product = $this->product_query->getByIdAndShopCollection($product_id, $shop);
        $product?->update([
            'optionIds' => array_unique(array_merge($product->getAttributeValue('optionIds'),
                [$recommended_product_id])),
        ]);

        $this->addReferenceProduct($recommended_product_id, $shop, $product_id);
    }

    /**
     * Delete a product of a shop.
     *
     * @param string $product_id
     * @param ShopCollection $shop
     * @return bool
     */
    public function delete(string $product_id, ShopCollection $shop): bool
    {
        $this->removeRelationshipRecommendation($product_id, $shop);

        $product = $shop->products()->where('id', $product_id)->first();
        $product?->delete();
        return true;
    }

    /**
     * Remove a relationship of a product with another product.
     *
     * @param string $product_id
     * @param ShopCollection $shop
     * @return void
     */
    private function removeRelationshipRecommendation(string $product_id, ShopCollection $shop): void
    {
        $product_collection = $this->product_query->getByIdAndShopCollection($product_id, $shop);
        if ($product_collection) {
            $this->removeRelationshipScore($shop, $product_collection, $product_id);

            $this->removeRelationshipRecommendationProduct($shop, $product_collection, $product_id);

            $this->removeRelationshipReferencedProduct($shop, $product_collection, $product_id);
        }
    }

    /**
     * Delete the score with a product with another product.
     *
     * @param ShopCollection $shop
     * @param ProductCollection $product
     * @param string $product_id
     * @return void
     */
    private function removeRelationshipScore(ShopCollection $shop, ProductCollection $product, string $product_id): void
    {
        $product_list_score = $this->relationship_score_query->getByProductCollection($product);

        foreach ($product_list_score as $relationship_score) {
            $this->relationship_score_command->deleteScore($shop, $relationship_score['productId'], $product_id);
        }
    }

    /**
     * Remove reference of this product to another product.
     *
     * @param ShopCollection $shop
     * @param ProductCollection $product
     * @param string $product_id
     * @return void
     */
    private function removeRelationshipRecommendationProduct(
        ShopCollection $shop,
        ProductCollection $product,
        string $product_id
    ): void {
        $product_recommendation_ids = $product->getAttributeValue('optionIds');

        foreach ($product_recommendation_ids as $product_recommendation_id) {
            $this->removeReferenceProduct($product_recommendation_id, $shop, $product_id);
        }
    }

    /**
     * Remove a reference this product using product for recommendation.
     *
     * @param string $product_id
     * @param ShopCollection $shop
     * @param string $reference_product_id
     * @return void
     */
    private function removeReferenceProduct(
        string $product_id,
        ShopCollection $shop,
        string $reference_product_id
    ): void {
        $product = $this->product_query->getByIdAndShopCollection($product_id, $shop);
        $product?->update([
            'referencedIds' => array_diff($product->getAttributeValue('referencedIds'), [$reference_product_id]),
        ]);
    }

    /**
     * Remove recommended product for this product from another product.
     *
     * @param ShopCollection $shop
     * @param ProductCollection $product
     * @param string $product_id
     * @return void
     */
    private function removeRelationshipReferencedProduct(
        ShopCollection $shop,
        ProductCollection $product,
        string $product_id
    ): void {
        $product_referenced_ids = $product->getAttributeValue('referencedIds');

        foreach ($product_referenced_ids as $product_referenced_id) {
            $this->removeProductRecommendation($product_referenced_id, $shop, $product_id);
        }
    }

    /**
     * Remove a product recommended for this product.
     *
     * @param string $product_id
     * @param ShopCollection $shop
     * @param string $recommended_product_id
     * @return void
     */
    public function removeProductRecommendation(
        string $product_id,
        ShopCollection $shop,
        string $recommended_product_id
    ): void {
        $product = $this->product_query->getByIdAndShopCollection($product_id, $shop);
        $product?->update([
            'optionIds' => array_diff($product->getAttributeValue('optionIds'), [$recommended_product_id]),
        ]);

        $this->removeReferenceProduct($recommended_product_id, $shop, $product_id);
    }
}
