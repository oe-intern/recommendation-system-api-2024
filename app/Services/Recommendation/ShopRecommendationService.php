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
    protected IShopCommand $shop_command;

    /**
     * @var IShopQuery
     */
    protected IShopQuery $shop_query;

    /**
     * @var IShopRecommendationQuery
     */
    protected IShopRecommendationQuery $shop_recommendation_query;

    /**
     * @var IShopRecommendationCommand
     */
    protected IShopRecommendationCommand $shop_recommendation_command;

    /**
     * @var IProductQueryShopify
     */
    protected IProductQueryShopify $product_query_shopify;

    /**
     * @var IJobRecommendationQuery
     */
    protected IJobRecommendationQuery $job_recommendation_query;

    /**
     * @var ProductTransform
     */
    protected ProductTransform $product_transform;

    /**
     * @var IRecommendationProcess
     */
    protected IRecommendationProcess $recommendation_process;

    /**
     * ShopRecommendationService constructor.
     *
     * @param IShopCommand $shop_command
     * @param IShopQuery $shop_query
     * @param IShopRecommendationQuery $shop_recommendation_query
     * @param IShopRecommendationCommand $shop_recommendation_command
     * @param IProductQueryShopify $product_query_shopify
     * @param IJobRecommendationQuery $job_recommendation_query
     * @param ShopifyTransform $product_transform
     * @param IRecommendationProcess $recommendation_process
     */
    public function __construct(
        IShopCommand $shop_command,
        IShopQuery $shop_query,
        IShopRecommendationQuery $shop_recommendation_query,
        IShopRecommendationCommand $shop_recommendation_command,
        IProductQueryShopify $product_query_shopify,
        IJobRecommendationQuery $job_recommendation_query,
        ShopifyTransform $product_transform,
        IRecommendationProcess $recommendation_process,
    ) {
        $this->shop_command = $shop_command;
        $this->shop_query = $shop_query;
        $this->shop_recommendation_query = $shop_recommendation_query;
        $this->shop_recommendation_command = $shop_recommendation_command;
        $this->product_query_shopify = $product_query_shopify;
        $this->job_recommendation_query = $job_recommendation_query;
        $this->product_transform = $product_transform;
        $this->recommendation_process = $recommendation_process;
    }

    /**
     * Refresh the recommendations products for a shop.
     *
     * @param string $shop_id
     * @param string $shop_domain
     * @return void
     *
     * @throws RecommendationRefreshLimitException
     * @throws JobRecommendationRunningException
     */
    public function refreshRecommendations(string $shop_id, string $shop_domain): void
    {
        $this->checkAfterRefresh($shop_id);
        $products = $this->getProductsData();
        $orders = $this->getOrdersData($shop_domain);

        ExecuteRecommendationPipelineJob::dispatch($shop_domain, $products, $orders);
    }

    /**
     * Get product data to install
     *
     * @return array
     */
    private function getProductsData(): array
    {
        $products_data = $this->product_query_shopify->fetchAll();
        return $this->product_transform->shopifyDataListToModelApiListData($products_data);
    }

    /**
     * Get order data to install
     *
     * @param string $domain
     * @return array
     */
    private function getOrdersData(string $domain): array
    {
        return $this->recommendation_process->processOrderData($domain);
    }

    /**
     * Check the shop recommendation after refresh.
     *
     * @param string $shop_id
     * @return void
     * @throws RecommendationRefreshLimitException
     * @throws JobRecommendationRunningException
     */
    private function checkAfterRefresh(string $shop_id): void
    {
        $this->checkAndReset($shop_id);
        $this->checkJobRunning($shop_id);
    }

    /**
     * Check and reset the recommendation count for a shop.
     *
     * @param string $shop_id
     * @return void
     *
     * @throws RecommendationRefreshLimitException
     */
    private function checkAndReset(string $shop_id): void
    {
        $shop_recommendation = $this->getShopRecommendation($shop_id);

        if ($this->isExpired($shop_recommendation)) {
            $this->shop_recommendation_command->resetRefreshRecommendation($shop_id);
        }

        if ($this->isLimitRefresh($shop_recommendation)) {
            throw new RecommendationRefreshLimitException();
        }
    }

    /**
     * Check if the job is running for a shop.
     *
     * @throws JobRecommendationRunningException
     */
    private function checkJobRunning(string $shop_id): void
    {
        $job_recommendation = $this->getLatestJobRecommendation($shop_id);

        if ($job_recommendation->getStatus()->equals(JobRecommendationStatus::PENDING)) {
            throw new JobRecommendationRunningException();
        }
    }

    /**
     * Get the shop recommendation instance.
     *
     * @param string $shop_id
     * @return ShopRecommendationSchema
     */
    private function getShopRecommendation(string $shop_id): ShopRecommendationSchema
    {
        return $this->shop_recommendation_query->getByShopId($shop_id);
    }

    /**
     * Check if the shop recommendation info is expired.
     *
     * @param ShopRecommendationSchema $shop_recommendation
     * @return bool
     */
    private function isExpired(ShopRecommendationSchema $shop_recommendation): bool
    {
        $current_time = Utils::getNow();
        $expires_at = $shop_recommendation->getExpiresAt();

        return $expires_at->lessThan($current_time);
    }

    /**
     * Check if the shop recommendation has reached the limit of refresh.
     *
     * @param ShopRecommendationSchema $shop_recommendation
     * @return bool
     */
    private function isLimitRefresh(ShopRecommendationSchema $shop_recommendation): bool
    {
        return $shop_recommendation->getRefreshCount() <= 0;
    }

    /**
     * Cancel the recommendations for a shop.
     *
     * @param string $shop_id
     * @return void
     */
    public function cancelRecommendations(string $shop_id): void
    {
        // TODO: Implement cancelRecommendations() method.
    }

    /**
     * Get the processing status of the recommendations for a shop.
     *
     * @param string $shop_id
     * @return array
     */
    public function getProcessingStatus(string $shop_id): array
    {
        $job_recommendation = $this->getLatestJobRecommendation($shop_id);

        return [
            'status' => $job_recommendation->getStatus(),
        ];
    }

    /**
     * Get the latest job recommendation for a shop.
     *
     * @param string $shop_id
     * @return JobRecommendationCollection
     */
    private function getLatestJobRecommendation(string $shop_id): JobRecommendationCollection
    {
        $shop_recommendation = $this->getShopRecommendation($shop_id);
        $last_job_id = $shop_recommendation->getLastJobRecommendationId();

        return $this->job_recommendation_query->getById($last_job_id);
    }

    /**
     * Get the shop recommendations information.
     *
     * @param string $shop_id
     * @return array
     */
    public function getShopRecommendations(string $shop_id): array
    {
        $shop_recommendation = $this->getShopRecommendation($shop_id);

        return [
            'refresh_count' => $shop_recommendation->getRefreshCount(),
            'expires_at' => $shop_recommendation->getExpiresAt(),
            'email' => $shop_recommendation->getEmail(),
            'email_notification' => $shop_recommendation->getEmailNotification(),
        ];
    }

    /**
     * Update the shop enable recommendation notification & email for shop recommendation.
     *
     * @param string $shop_id
     * @param array $notification_data
     * @return array
     */
    public function updateShopRecommendationNotification(string $shop_id, array $notification_data): array
    {
        $email = data_get($notification_data, 'email');
        $email_notification = data_get($notification_data, 'email_notification');

        $this->shop_recommendation_command->updateNotification($shop_id, $email_notification, $email);
        $shop_recommendation = $this->getShopRecommendation($shop_id);

        return [
            'email' => $shop_recommendation->getEmail(),
            'email_notification' => $shop_recommendation->getEmailNotification(),
        ];
    }
}
