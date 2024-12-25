<?php

namespace App\Contracts\Queries;

use App\Collections\JobRecommendationCollection;
use App\Collections\ShopCollection;

interface  IJobRecommendationQuery
{
    /**
     * Get the job recommendation by job id.
     *
     * @param string $jobId
     * @return JobRecommendationCollection
     */
    public function getById(string $jobId): JobRecommendationCollection;

    /**
     * Get last job recommendation by shop id.
     *
     * @param string $shopId
     * @return JobRecommendationCollection
     */
    public function getLastByShopId(string $shopId): JobRecommendationCollection;

    /**
     * Get last job recommendation by Shop collection.
     *
     * @param ShopCollection $shop
     * @return JobRecommendationCollection
     */
    public function getLastByShop(ShopCollection $shop): JobRecommendationCollection;
}
