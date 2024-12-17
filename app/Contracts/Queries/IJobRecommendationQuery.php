<?php

namespace App\Contracts\Queries;

use App\Collections\JobRecommendationCollection;
use App\Collections\ShopCollection;

interface  IJobRecommendationQuery
{
    /**
     * Get the job recommendation by job id.
     *
     * @param string $job_id
     * @return JobRecommendationCollection
     */
    public function getById(string $job_id): JobRecommendationCollection;

    /**
     * Get last job recommendation by shop id.
     *
     * @param string $shop_id
     * @return JobRecommendationCollection
     */
    public function getLastByShopId(string $shop_id): JobRecommendationCollection;


    /**
     * Get last job recommendation by Shop collection.
     *
     * @param ShopCollection $shop
     * @return JobRecommendationCollection
     */
    public function getLastByShop(ShopCollection $shop): JobRecommendationCollection;
}
