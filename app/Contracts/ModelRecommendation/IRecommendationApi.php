<?php

namespace App\Contracts\ModelRecommendation;

use Exception;

interface IRecommendationApi
{
    /**
     * Call the external recommend endpoint.
     *
     * @param int $total_order
     * @param array $type_scores
     * @param array $product_scores
     * @param array $products
     * @return array
     *
     * @throws Exception
     */
    public function recommend(int $total_order, array $type_scores, array $product_scores, array $products): array;

    /**
     * Call the external pre-recommend endpoint.
     *
     * @param int $total_order
     * @param array $type_scores
     * @param array $product_scores
     * @param array $products
     * @return array
     *
     * @throws Exception
     */
    public function preRecommend(int $total_order, array $type_scores, array $product_scores, array $products): array;
}
