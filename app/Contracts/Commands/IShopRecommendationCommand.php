<?php

namespace App\Contracts\Commands;

interface IShopRecommendationCommand
{
    /**
     * Decrease the refresh recommendation count.
     *
     * @param string $shop_id
     * @return void
     */
    public function decreaseRefreshRecommendation(string $shop_id): void;

    /**
     * Reset the refresh recommendation count.
     *
     * @param string $shop_id
     * @return void
     */
    public function resetRefreshRecommendation(string $shop_id): void;

    /**
     * Update the last job recommendation by shop id.
     *
     * @param string $shop_id
     * @param string $job_recommendation_id
     *
     * @return bool
     */
    public function updateLastJobRecommendation(string $shop_id, string $job_recommendation_id): bool;

    /**
     * Update the email notification status & email address for the recommendation.
     *
     * @param string $shop_id
     * @param bool $email_notification
     * @param string|null $email
     *
     * @return bool
     */
    public function updateNotification(string $shop_id, bool $email_notification, ?string $email): bool;
}
