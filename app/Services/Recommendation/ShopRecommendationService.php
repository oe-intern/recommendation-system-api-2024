<?php

namespace App\Services\Recommendation;

use App\Collections\JobRecommendationCollection;
use App\Collections\Schema\ShopRecommendationSchema;
use App\Contracts\Commands\IShopCommand;
use App\Contracts\Commands\IShopRecommendationCommand;
use App\Contracts\Objects\Transform\ShopifyTransform;
use App\Contracts\Queries\IJobRecommendationQuery;
use App\Contracts\Queries\IShopQuery;
use App\Contracts\Queries\IShopRecommendationQuery;
use App\Contracts\Recommendation\IRecommendationProcess;
use App\Contracts\Recommendation\IShopRecommendation;
use App\Contracts\Shopify\Graphql\Queries\IProductQueryShopify;
use App\DTO\Request\UpdateNotificationSettingsRequestDTO;
use App\Exceptions\JobRecommendationRunningException;
use App\Exceptions\RecommendationRefreshLimitException;
use App\Jobs\ExecuteRecommendationPipelineJob;
use App\Lib\Utils;
use App\Objects\Enums\JobRecommendationStatus;
use App\Objects\Transform\ProductTransform;

class ShopRecommendationService implements IShopRecommendation
{
    /**
     * @var IShopCommand
     */
    protected IShopCommand $shopCommand;

    /**
     * @var IShopQuery
     */
    protected IShopQuery $shopQuery;

    /**
     * @var IShopRecommendationQuery
     */
    protected IShopRecommendationQuery $shopRecommendationQuery;

    /**
     * @var IShopRecommendationCommand
     */
    protected IShopRecommendationCommand $shopRecommendationCommand;

    /**
     * @var IProductQueryShopify
     */
    protected IProductQueryShopify $productQueryShopify;

    /**
     * @var IJobRecommendationQuery
     */
    protected IJobRecommendationQuery $jobRecommendationQuery;

    /**
     * @var ProductTransform
     */
    protected ProductTransform $productTransform;

    /**
     * @var IRecommendationProcess
     */
    protected IRecommendationProcess $recommendationProcess;

    /**
     * ShopRecommendationService constructor.
     *
     * @param IShopCommand $shopCommand
     * @param IShopQuery $shopQuery
     * @param IShopRecommendationQuery $shopRecommendationQuery
     * @param IShopRecommendationCommand $shopRecommendationCommand
     * @param IProductQueryShopify $productQueryShopify
     * @param IJobRecommendationQuery $jobRecommendationQuery
     * @param ShopifyTransform $productTransform
     * @param IRecommendationProcess $recommendationProcess
     */
    public function __construct(
        IShopCommand $shopCommand,
        IShopQuery $shopQuery,
        IShopRecommendationQuery $shopRecommendationQuery,
        IShopRecommendationCommand $shopRecommendationCommand,
        IProductQueryShopify $productQueryShopify,
        IJobRecommendationQuery $jobRecommendationQuery,
        ShopifyTransform $productTransform,
        IRecommendationProcess $recommendationProcess,
    ) {
        $this->shopCommand = $shopCommand;
        $this->shopQuery = $shopQuery;
        $this->shopRecommendationQuery = $shopRecommendationQuery;
        $this->shopRecommendationCommand = $shopRecommendationCommand;
        $this->productQueryShopify = $productQueryShopify;
        $this->jobRecommendationQuery = $jobRecommendationQuery;
        $this->productTransform = $productTransform;
        $this->recommendationProcess = $recommendationProcess;
    }

    /**
     * Refresh the recommendations products for a shop.
     *
     * @param string $shopId
     * @param string $shopDomain
     * @return void
     *
     * @throws RecommendationRefreshLimitException
     * @throws JobRecommendationRunningException
     */
    public function refreshRecommendations(string $shopId, string $shopDomain): void
    {
        $this->validateRefreshRequest($shopId);

        $products = $this->fetchProducts();

        ExecuteRecommendationPipelineJob::dispatch($shopDomain, $products)
            ->onQueue(config('queue.queues.recommendation'));
    }

    /**
     * Get product data to install
     *
     * @return array
     */
    private function fetchProducts(): array
    {
        return $this->productQueryShopify->fetchAll();
    }

    /**
     * Check the shop recommendation after refresh.
     *
     * @param string $shopId
     * @return void
     * @throws RecommendationRefreshLimitException
     * @throws JobRecommendationRunningException
     */
    private function validateRefreshRequest(string $shopId): void
    {
        $shopRecommendation = $this->getShopRecommendation($shopId);
        $this->checkAndResetRecommendation($shopRecommendation, $shopId);
        $this->checkJobRunning($shopId);
    }

    /**
     * Check and reset the recommendation count for a shop.
     *
     * @param ShopRecommendationSchema $shopRecommendation
     * @param string $shopId
     * @return void
     *
     * @throws RecommendationRefreshLimitException
     */
    private function checkAndResetRecommendation(ShopRecommendationSchema $shopRecommendation, string $shopId): void
    {
        if ($this->isExpired($shopRecommendation)) {
            $this->shopRecommendationCommand->resetRefreshRecommendation($shopId);
        }

        if ($this->isLimitRefresh($shopRecommendation)) {
            throw new RecommendationRefreshLimitException();
        }
    }

    /**
     * Check if the job is running for a shop.
     *
     * @throws JobRecommendationRunningException
     */
    private function checkJobRunning(string $shopId): void
    {
        $jobRecommendation = $this->getLatestJobRecommendation($shopId);

        if ($jobRecommendation->getStatus()->equals(JobRecommendationStatus::PENDING)) {
            throw new JobRecommendationRunningException();
        }
    }

    /**
     * Get the shop recommendation instance.
     *
     * @param string $shopId
     * @return ShopRecommendationSchema
     */
    private function getShopRecommendation(string $shopId): ShopRecommendationSchema
    {
        return $this->shopRecommendationQuery->getByShopId($shopId);
    }

    /**
     * Check if the shop recommendation info is expired.
     *
     * @param ShopRecommendationSchema $shopRecommendation
     * @return bool
     */
    private function isExpired(ShopRecommendationSchema $shopRecommendation): bool
    {
        $currentTime = Utils::getNow();
        $expiresAt = $shopRecommendation->getExpiresAt();

        return $expiresAt->lessThan($currentTime);
    }

    /**
     * Check if the shop recommendation has reached the limit of refresh.
     *
     * @param ShopRecommendationSchema $shopRecommendation
     * @return bool
     */
    private function isLimitRefresh(ShopRecommendationSchema $shopRecommendation): bool
    {
        return $shopRecommendation->getRefreshCount() <= 0;
    }

    /**
     * Cancel the recommendations for a shop.
     *
     * @param string $shopId
     * @return void
     */
    public function cancelRecommendations(string $shopId): void
    {
        // TODO: Implement cancelRecommendations() method.
    }

    /**
     * Get the processing status of the recommendations for a shop.
     *
     * @param string $shopId
     * @return array
     */
    public function getProcessingStatus(string $shopId): array
    {
        $jobRecommendation = $this->getLatestJobRecommendation($shopId);

        return [
            'status' => $jobRecommendation->getStatus(),
        ];
    }

    /**
     * Get the latest job recommendation for a shop.
     *
     * @param string $shopId
     * @return JobRecommendationCollection
     */
    private function getLatestJobRecommendation(string $shopId): JobRecommendationCollection
    {
        $shopRecommendation = $this->getShopRecommendation($shopId);
        $lastJobId = $shopRecommendation->getLastJobRecommendationId();

        return $this->jobRecommendationQuery->getById($lastJobId);
    }

    /**
     * Get the shop recommendations information.
     *
     * @param string $shopId
     * @return array
     */
    public function getShopRecommendations(string $shopId): array
    {
        $shopRecommendation = $this->getShopRecommendation($shopId);

        return [
            'refresh_count' => $shopRecommendation->getRefreshCount(),
            'expires_at' => $shopRecommendation->getExpiresAt(),
            'email' => $shopRecommendation->getEmail(),
            'email_notification' => $shopRecommendation->getEmailNotification(),
        ];
    }

    /**
     * Update the shop enable recommendation notification & email for shop recommendation.
     *
     * @param string $shopId
     * @param UpdateNotificationSettingsRequestDTO $requestDTO
     * @return array
     */
    public function updateShopRecommendationNotification(
        string $shopId,
        UpdateNotificationSettingsRequestDTO $requestDTO,
    ): array {
        $this->shopRecommendationCommand->updateNotification(
            $shopId,
            $requestDTO->emailNotification,
            $requestDTO->email,
        );
        $shopRecommendation = $this->getShopRecommendation($shopId);

        return [
            'email' => $shopRecommendation->getEmail(),
            'email_notification' => $shopRecommendation->getEmailNotification(),
        ];
    }
}
