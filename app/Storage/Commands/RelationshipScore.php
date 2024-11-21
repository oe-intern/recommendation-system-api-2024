<?php

namespace App\Storage\Commands;

use App\Collections\Product as ProductCollection;
use App\Collections\Schema\RelationshipScore as RelationshipScoreSchema;
use App\Collections\Shop as ShopCollection;
use App\Contracts\Commands\RelationshipScore as RelationshipScoreCommand;
use App\Contracts\Queries\Product as ProductQuery;

class RelationshipScore implements RelationshipScoreCommand
{
    /**
     * @var ProductQuery
     */
    protected ProductQuery $product_query;

    /**
     * RelationshipScore constructor.
     *
     * @param ProductQuery $product_query
     */
    public function __construct(ProductQuery $product_query)
    {
        $this->product_query = $product_query;
    }

    /**
     * Set the score of a relationship between two products from a shop.
     *
     * @param ShopCollection $shop
     * @param string $first_product_id
     * @param string $second_product_id
     * @param float $score
     * @return void
     */
    public function setScore(
        ShopCollection $shop,
        string $first_product_id,
        string $second_product_id,
        float $score = 1
    ): void {
        $first_product = $this->product_query->getByIdAndShopCollection($first_product_id, $shop);
        $second_product = $this->product_query->getByIdAndShopCollection($second_product_id, $shop);

        if ($first_product && $second_product) {
            $this->saveRelationshipScore($first_product, $second_product_id, $score);
            $this->saveRelationshipScore($second_product, $first_product_id, $score);
        }
    }

    /**
     * Save the relationship score between two products.
     *
     * @param ProductCollection $product
     * @param string $product_id
     * @param float $score
     * @return void
     */
    private function saveRelationshipScore(ProductCollection $product, string $product_id, float $score): void
    {
        $product->relationshipScore()->save(new RelationshipScoreSchema([
            'productId' => $product_id,
            'score' => $score
        ]));
    }

    /**
     * Delete the score with a product with another product.
     *
     * @param ShopCollection $shop
     * @param string $root_product_id
     * @param string $related_product_id
     * @return void
     */
    public function deleteScore(ShopCollection $shop, string $root_product_id, string $related_product_id): void
    {
        $root_product = $this->product_query->getByIdAndShopCollection($root_product_id, $shop);

        if ($root_product) {
            $relationshipScore = $root_product->relationshipScore()->where('productId', $related_product_id)->first();
            $relationshipScore?->delete();
        }
    }
}
