<?php

namespace App\Storage\Queries;

use App\Collections\JobRecommendationCollection;
use App\Collections\ShopCollection;
use App\Contracts\Queries\IJobRecommendationQuery;
use App\Contracts\Queries\IShopQuery;

class JobRecommendationQuery implements IJobRecommendationQuery
{
    /**
     * @var IShopQuery
     */
    protected IShopQuery $shop_query;

    /**
     * JobRecommendationQuery constructor.
     *
     * @param IShopQuery $shop_query
     */
    public function __construct(IShopQuery $shop_query)
    {
        $this->shop_query = $shop_query;
    }

    /**
     * Get last job recommendation by shop id.
     *
     * @param string $shop_id
     * @return JobRecommendationCollection
     */
    public function getLastByShopId(string $shop_id): JobRecommendationCollection
    {
        $shop = $this->shop_query->getById($shop_id);
        return $this->getLastByShop($shop);
    }

    /**
     * Get the job recommendation by job id.
     *
     * @param string $job_id
     * @return JobRecommendationCollection
     */
    public function getById(string $job_id): JobRecommendationCollection
    {
        return JobRecommendationCollection::query()
            ->where('_id', $job_id)
            ->first();
    }

    /**
     * Get last job recommendation by Shop collection.
     *
     * @param ShopCollection $shop
     * @return JobRecommendationCollection
     */
    public function getLastByShop(ShopCollection $shop): JobRecommendationCollection
    {
        $shop_recommendation = $shop->shopRecommendation()->get();
        $job_recommendation_id = $shop_recommendation->getLastJobRecommendationId();

        return $this->getById($job_recommendation_id);
    }
}
