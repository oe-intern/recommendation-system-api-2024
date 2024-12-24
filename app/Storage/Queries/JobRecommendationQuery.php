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
    protected IShopQuery $shopQuery;

    /**
     * JobRecommendationQuery constructor.
     *
     * @param IShopQuery $shopQuery
     */
    public function __construct(IShopQuery $shopQuery)
    {
        $this->shopQuery = $shopQuery;
    }

    /**
     * Get last job recommendation by shop id.
     *
     * @param string $shopId
     * @return JobRecommendationCollection
     */
    public function getLastByShopId(string $shopId): JobRecommendationCollection
    {
        $shop = $this->shopQuery->getById($shopId);
        return $this->getLastByShop($shop);
    }

    /**
     * Get the job recommendation by job id.
     *
     * @param string $jobId
     * @return JobRecommendationCollection
     */
    public function getById(string $jobId): JobRecommendationCollection
    {
        return JobRecommendationCollection::query()
            ->where('_id', $jobId)
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
        $shopRecommendation = $shop->shopRecommendation()->get();
        $recommendationJobId = $shopRecommendation->getLastJobRecommendationId();

        return $this->getById($recommendationJobId);
    }
}
