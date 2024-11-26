<?php

namespace App\Contracts\Commands;

interface RelationshipScore
{
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
        float $score
    ): void;

    /**
     * Delete the score with a product with another product.
     *
     * @param string $root_product_id
     * @param string $related_product_id
     * @return void
     */
    public function deleteScore(string $root_product_id, string $related_product_id): void;
}
