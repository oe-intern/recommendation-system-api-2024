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
    protected IShopQuery $shop_query;

    /**
     * ShopRecommendationCommand constructor.
     *
     * @param IShopQuery $shop_query
     */
    public function __construct(IShopQuery $shop_query)
    {
        $this->shop_query = $shop_query;
    }

    /**
     * Decrease the refresh recommendation count.
     *
     * @param string $shop_id
     * @return void
     */
    public function decreaseRefreshRecommendation(string $shop_id): void
    {
        $shop_recommendation = $this->getShopRecommendation($shop_id);
        $this->getShopRecommendation($shop_id)->update([
            'refresh_count' => $shop_recommendation->getRefreshCount() - 1,
        ]);
    }

    /**
     * Get the shop recommendation by shop id.
     *
     * @param string $shop_id
     * @return ShopRecommendationSchema
     */
    private function getShopRecommendation(string $shop_id): ShopRecommendationSchema
    {
        $shop = $this->shop_query->getById($shop_id);
        return $shop->shopRecommendation()->get();
    }

    /**
     * Reset the refresh recommendation count.
     *
     * @param string $shop_id
     * @return void
     */
    public function resetRefreshRecommendation(string $shop_id): void
    {
        $this->getShopRecommendation($shop_id)->update([
            'refresh_count' => config('services.recommendation.refresh_limit'),
            'expires_at' => Utils::refreshDay(),
        ]);
    }

    /**
     * Update the last job recommendation by shop id.
     *
     * @param string $shop_id
     * @param string $job_recommendation_id
     *
     * @return bool
     */
    public function updateLastJobRecommendation(string $shop_id, string $job_recommendation_id): bool
    {
        return $this->getShopRecommendation($shop_id)->update([
            'last_job_recommendation_id' => $job_recommendation_id,
        ]);
    }

    /**
     * Update the email notification status & email address for the recommendation.
     *
     * @param string $shop_id
     * @param bool $email_notification
     * @param string|null $email
     *
     * @return bool
     */
    public function updateNotification(string $shop_id, bool $email_notification, ?string $email): bool
    {
        Log::info('updateNotification', [
            'shop_id' => $shop_id,
            'email_notification' => $email_notification,
            'email' => $email,
        ]);
        return $this->getShopRecommendation($shop_id)->update([
            'email_notification' => $email_notification,
            'email' => $email ?? '',
        ]);
    }
}
