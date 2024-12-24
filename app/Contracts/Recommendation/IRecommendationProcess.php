<?php

namespace App\Contracts\Recommendation;

interface IRecommendationProcess
{
    /**
     * Process order data.
     *
     * @param string $shopId
     * @return array
     */
    public function processOrderData(string $shopId): array;

    /**
     * Process pre-recommendation data.
     *
     * @param string $shopId
     * @return array
     */
    public function processPreRecommendationData(string $shopId): array;
}
