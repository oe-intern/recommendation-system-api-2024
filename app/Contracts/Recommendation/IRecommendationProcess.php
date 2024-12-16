<?php

namespace App\Contracts\Recommendation;

interface IRecommendationProcess
{
    /**
     * Process order data.
     *
     * @param string $shop_id
     * @return array
     */
    public function processOrderData(string $shop_id): array;

    /**
     * Process pre-recommendation data.
     *
     * @param string $shop_id
     * @return array
     */
    public function processPreRecommendationData(string $shop_id): array;
}
