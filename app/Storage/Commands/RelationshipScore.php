<?php

namespace App\Storage\Commands;

use App\Collections\Product as ProductCollection;
use App\Collections\Schema\RelationshipScore as RelationshipScoreSchema;
use App\Collections\Shop as ShopCollection;
use App\Contracts\Commands\RelationshipScore as RelationshipScoreCommand;

class RelationshipScore implements RelationshipScoreCommand
{
    /**
     * Increment the score of a relationship between two products from a shop.
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
        $product1 = $this->getProduct($shop, $first_product_id);
        $product2 = $this->getProduct($shop, $second_product_id);

        if ($product1 && $product2) {
            $this->saveRelationshipScore($product1, $second_product_id, $score);
            $this->saveRelationshipScore($product2, $first_product_id, $score);
        }
    }

    /**
     * Get a product from a shop.
     *
     * @param ShopCollection $shop
     * @param string $product_id
     * @return ProductCollection|null
     */
    private function getProduct(ShopCollection $shop, string $product_id): ?ProductCollection
    {
        return $shop->products()->where('id', $product_id)->first();
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
}
