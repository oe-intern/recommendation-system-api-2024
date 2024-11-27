<?php

namespace App\Storage\Commands;

use App\Collections\ProductCollection;
use App\Collections\Schema\RelationshipScoreSchema;
use App\Contracts\Commands\IRelationshipScoreCommand;
use App\Contracts\Queries\IProductQuery;

class RelationshipScoreCommand implements IRelationshipScoreCommand
{
    /**
     * @var IProductQuery
     */
    protected IProductQuery $product_query;

    /**
     * RelationshipScore constructor.
     *
     * @param IProductQuery $product_query
     */
    public function __construct(IProductQuery $product_query)
    {
        $this->product_query = $product_query;
    }

    /**
     * Set the score of a relationship between two products from a shop.
     *
     * @param string $first_product_id
     * @param string $second_product_id
     * @param float $score
     * @return void
     */
    public function setScore(
        string $first_product_id,
        string $second_product_id,
        float $score = 1
    ): void {
        $first_product = $this->product_query->getById($first_product_id);
        $second_product = $this->product_query->getById($second_product_id);

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
     * @param string $root_product_id
     * @param string $related_product_id
     * @return void
     */
    public function deleteScore(string $root_product_id, string $related_product_id): void
    {
        $root_product = $this->product_query->getById($root_product_id);

        if ($root_product) {
            $relationshipScore = $root_product->relationshipScore()->where('productId', $related_product_id)->first();
            $relationshipScore?->delete();
        }
    }
}
