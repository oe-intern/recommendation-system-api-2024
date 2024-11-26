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
     * @param ProductCollection $product
     * @param array $product_data
     * @return bool
     */
    public function update(ProductCollection $product, array $product_data): bool
    {
        return $product->update($product_data);
    }

    /**
     * Customize the recommendation products for each product.
     *
     * @param ProductCollection $product
     * @param string $shop_domain
     * @param RecommendationType $recommendation_type
     * @param array $recommendations
     * @return bool
     */
    public function setRecommendationProduct(
        ProductCollection $product,
        string $shop_domain,
        RecommendationType $recommendation_type,
        array $recommendations
    ): bool {
        $product_id = $product->getAttributeValue('id');
        $recommendations = $this->getValidRecommendationProducts($shop_domain, $product_id, $recommendations);
        $removed_recommendations = array_diff($product->getAttributeValue('optionIds'), $recommendations);

        $product->update([
            'recommendationType' => $recommendation_type,
            'optionIds' => $recommendations,
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
     * Get list of products valid for recommendation.
     *
     * @param string $shop_domain
     * @param string $product_id
     * @param array $product_ids
     * @return array
     */
    private function getValidRecommendationProducts(string $shop_domain, string $product_id, array $product_ids): array
    {
        $filtered_ids = array_unique(array_filter($product_ids, fn($id) => (string)$id !== $product_id));

        return $this->product_query->getByShopDomainAndIds($shop_domain, $filtered_ids);
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
            'referencedIds' => array_unique(array_merge($product->getAttributeValue('referencedIds'),
                [$reference_product_id])),
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
            'optionIds' => array_unique(array_merge($product->getAttributeValue('optionIds'),
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
        $this->removeRelationshipScore($product);

        $this->removeRelationshipRecommendationProduct($product);

        $this->removeRelationshipReferencedProduct($product);
    }

    /**
     * Delete the score with a product with another product.
     *
     * @param ProductCollection $product
     * @return void
     */
    private function removeRelationshipScore(ProductCollection $product): void
    {
        $product_id = $product->getAttributeValue('id');
        $product_list_score = $this->relationship_score_query->getByProductCollection($product);

        foreach ($product_list_score as $relationship_score) {
            $this->relationship_score_command->deleteScore($relationship_score['productId'], $product_id);
        }
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
        $product_id = $product->getAttributeValue('id');
        $product_recommendation_ids = $product->getAttributeValue('optionIds');

        foreach ($product_recommendation_ids as $product_recommendation_id) {
            $this->removeReferenceProduct($product_recommendation_id, $product_id);
        }
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
            'referencedIds' => array_diff($product->getAttributeValue('referencedIds'), [$reference_product_id]),
        ]);
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
        $product_id = $product->getAttributeValue('id');
        $product_referenced_ids = $product->getAttributeValue('referencedIds');

        foreach ($product_referenced_ids as $product_referenced_id) {
            $this->removeProductRecommendation($product_referenced_id, $product_id);
        }
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
            'optionIds' => array_diff($product->getAttributeValue('optionIds'), [$recommended_product_id]),
        ]);

        $this->removeReferenceProduct($recommended_product_id, $product_id);
    }
}
