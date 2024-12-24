<?php

namespace App\Contracts\Commands;

interface IShopRecommendationCommand
{
    /**
     * Decrease the refresh recommendation count.
     *
     * @param string $shopId
     * @return void
     */
    public function decreaseRefreshRecommendation(string $shopId): void;

    /**
     * Reset the refresh recommendation count.
     *
     * @param string $shopId
     * @return void
     */
    public function resetRefreshRecommendation(string $shopId): void;

    /**
     * Update the last job recommendation by shop id.
     *
     * @param string $shopId
     * @param string $recommendationJobId
     *
     * @return bool
     */
    public function updateLastJobRecommendation(string $shopId, string $recommendationJobId): bool;

    /**
     * Update the email notification status & email address for the recommendation.
     *
     * @param string $shopId
     * @param bool $emailNotification
     * @param string|null $email
     *
     * @return bool
     */
    public function updateNotification(string $shopId, bool $emailNotification, ?string $email): bool;
}
