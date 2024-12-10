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
}
