<?php

namespace App\Storage\Commands;

use App\Collections\Schema\ShopRecommendationSchema;
use App\Contracts\Commands\IShopRecommendationCommand;
use App\Contracts\Queries\IShopQuery;
use App\Lib\Utils;
use Illuminate\Support\Facades\Log;

class ShopRecommendationCommand implements IShopRecommendationCommand
{
    /**
     * @var IShopQuery
     */
    protected IShopQuery $shopQuery;

    /**
     * ShopRecommendationCommand constructor.
     *
     * @param IShopQuery $shopQuery
     */
    public function __construct(IShopQuery $shopQuery)
    {
        $this->shopQuery = $shopQuery;
    }

    /**
     * Decrease the refresh recommendation count.
     *
     * @param string $shopId
     * @return void
     */
    public function decreaseRefreshRecommendation(string $shopId): void
    {
        $shopRecommendation = $this->getShopRecommendation($shopId);
        $this->getShopRecommendation($shopId)->update([
            'refresh_count' => $shopRecommendation->getRefreshCount() - 1,
        ]);
    }

    /**
     * Get the shop recommendation by shop id.
     *
     * @param string $shopId
     * @return ShopRecommendationSchema
     */
    private function getShopRecommendation(string $shopId): ShopRecommendationSchema
    {
        $shop = $this->shopQuery->getById($shopId);
        return $shop->shopRecommendation()->get();
    }

    /**
     * Reset the refresh recommendation count.
     *
     * @param string $shopId
     * @return void
     */
    public function resetRefreshRecommendation(string $shopId): void
    {
        $this->getShopRecommendation($shopId)->update([
            'refresh_count' => config('services.recommendation.refresh_limit'),
            'expires_at' => Utils::refreshDay(),
        ]);
    }

    /**
     * Update the last job recommendation by shop id.
     *
     * @param string $shopId
     * @param string $recommendationJobId
     *
     * @return bool
     */
    public function updateLastJobRecommendation(string $shopId, string $recommendationJobId): bool
    {
        return $this->getShopRecommendation($shopId)->update([
            'last_recommendation_job_id' => $recommendationJobId,
        ]);
    }

    /**
     * Update the email notification status & email address for the recommendation.
     *
     * @param string $shopId
     * @param bool $emailNotification
     * @param string|null $email
     *
     * @return bool
     */
    public function updateNotification(string $shopId, bool $emailNotification, ?string $email): bool
    {
        return $this->getShopRecommendation($shopId)->update([
            'email_notification' => $emailNotification,
            'email' => $email ?? '',
        ]);
    }
}
