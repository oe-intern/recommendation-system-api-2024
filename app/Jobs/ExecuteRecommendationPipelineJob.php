<?php


namespace App\Jobs;

use App\Actions\UpdateDefaultRecommendation;
use App\Collections\JobRecommendationCollection;
use App\Contracts\Commands\IJobRecommendationCommand;
use App\Contracts\Commands\IShopRecommendationCommand;
use App\Contracts\Mail\IEmailSender;
use App\Contracts\ModelRecommendation\IRecommendationApi;
use App\Contracts\Objects\Transform\ShopifyTransform;
use App\Contracts\Queries\IProductQuery;
use App\Contracts\Queries\IShopQuery;
use App\Contracts\Queries\IShopRecommendationQuery;
use App\Contracts\Recommendation\IProductRecommendation;
use App\Contracts\Recommendation\IRecommendationProcess;
use App\DTO\Payload\ShopProductRecommendationRequestDTO;
use App\Models\User;
use App\Objects\Enums\JobRecommendationStatus;
use App\Objects\Transform\ProductTransform;
use App\Services\Shopify\UserContext;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class ExecuteRecommendationPipelineJob implements ShouldQueue
{
    use Queueable, Dispatchable;

    /**
     * @var int
     */
    private const MAX_RECOMMENDATION_PRODUCTS = 6;

    /**
     * @var int
     */
    private const MAX_RETRY_PROCESS = 5;

    /**
     * @var int
     */
    private const DELAY_RECOMMEND_PROCESS = 20;

    /**
     * @var UpdateDefaultRecommendation
     */
    private UpdateDefaultRecommendation $updateDefaultRecommendation;

    /**
     * @var string
     */
    protected string $domain;

    /**
     * @var array
     */
    protected array $productsData;

    /**
     * @var array
     */
    protected array $ordersData;

    /**
     * @var IProductQuery
     */
    protected IProductQuery $productQuery;

    /**
     * @var IShopQuery
     */
    protected IShopQuery $shopQuery;

    /**
     * @var IShopRecommendationCommand
     */
    protected IShopRecommendationCommand $shopRecommendationCommand;

    /**
     * @var IProductRecommendation
     */
    protected IProductRecommendation $productRecommendationService;

    /**
     * @var IRecommendationApi
     */
    protected IRecommendationApi $recommendationApiService;

    /**
     * @var IJobRecommendationCommand
     */
    protected IJobRecommendationCommand $jobRecommendationCommand;

    /**
     * @var IEmailSender
     */
    protected IEmailSender $emailSenderService;

    /**
     * @var ProductTransform
     */
    protected ProductTransform $productTransform;

    /**
     * @var IRecommendationProcess
     */
    protected IRecommendationProcess $recommendationProcess;

    /**
     * @var IShopRecommendationQuery
     */
    protected IShopRecommendationQuery $shopRecommendationQuery;

    /**
     * Create a new job instance.
     *
     * @param string $domain
     * @param array $productsData
     */
    public function __construct(string $domain, array $productsData)
    {
        $this->domain = $domain;
        $this->productsData = $productsData;
    }

    /**
     * Execute the job.
     *
     * @param IProductQuery $productQuery
     * @param IShopQuery $shopQuery
     * @param IShopRecommendationCommand $shopRecommendationCommand
     * @param IProductRecommendation $productRecommendationService
     * @param IRecommendationApi $recommendationApiService
     * @param IJobRecommendationCommand $jobRecommendationCommand
     * @param IEmailSender $emailSenderService
     * @param ShopifyTransform $productTransform
     * @param IRecommendationProcess $recommendationProcess
     * @param UpdateDefaultRecommendation $updateDefaultRecommendation
     * @param IShopRecommendationQuery $shopRecommendationQuery
     */
    public function handle(
        IProductQuery $productQuery,
        IShopQuery $shopQuery,
        IShopRecommendationCommand $shopRecommendationCommand,
        IProductRecommendation $productRecommendationService,
        IRecommendationApi $recommendationApiService,
        IJobRecommendationCommand $jobRecommendationCommand,
        IEmailSender $emailSenderService,
        ShopifyTransform $productTransform,
        IRecommendationProcess $recommendationProcess,
        UpdateDefaultRecommendation $updateDefaultRecommendation,
        IShopRecommendationQuery $shopRecommendationQuery,
    ): void {
        $this->initializeServices(
            $productQuery,
            $shopQuery,
            $shopRecommendationCommand,
            $productRecommendationService,
            $recommendationApiService,
            $jobRecommendationCommand,
            $emailSenderService,
            $productTransform,
            $recommendationProcess,
            $updateDefaultRecommendation,
            $shopRecommendationQuery,
        );

        $this->ordersData = $this->getOrdersData($this->domain);
        $this->productsData = $this->productTransform->shopifyDataListToModelApiListData($this->productsData);
        $shop = $shopQuery->getByDomain($this->domain);
        $shopId = $shop->getId();

        $dataRequest = $this->getRequestData();
        $gidToIdMap = $this->getMapIdWithKeyGid($shopId);

        if ($this->isDefaultRecommendationOnly($shopId)) {
            $this->updateDefaultRecommendation($dataRequest, $gidToIdMap);

            return;
        }

        $job = $this->createPendingJob($shopId);

        $this->retry(function () use ($dataRequest, $gidToIdMap, $job, $shopId) {
            $this->updateDefaultRecommendation($dataRequest, $gidToIdMap);
            $this->updateJobStatus($job->getId(), JobRecommendationStatus::SUCCESS, []);

            ProcessShopInstalledData::dispatch(
                $gidToIdMap,
                $this->productsData,
                $shopId,
                $this->domain,
                $dataRequest,
            )->onQueue(config('queue.queues.recommendation'));
        });

        $this->handleJobException($job->getId(), $shopId, $this->domain);
    }

    /**
     * Initialize services.
     *
     * @param IProductQuery $productQuery
     * @param IShopQuery $shopQuery
     * @param IShopRecommendationCommand $shopRecommendationCommand
     * @param IProductRecommendation $productRecommendationService
     * @param IRecommendationApi $recommendationApiService
     * @param IJobRecommendationCommand $jobRecommendationCommand
     * @param IEmailSender $emailSenderService
     * @param ShopifyTransform $productTransform
     * @param IRecommendationProcess $recommendationProcess
     * @param UpdateDefaultRecommendation $updateDefaultRecommendation
     * @param IShopRecommendationQuery $shopRecommendationQuery
     * @return void
     */
    private function initializeServices(
        IProductQuery $productQuery,
        IShopQuery $shopQuery,
        IShopRecommendationCommand $shopRecommendationCommand,
        IProductRecommendation $productRecommendationService,
        IRecommendationApi $recommendationApiService,
        IJobRecommendationCommand $jobRecommendationCommand,
        IEmailSender $emailSenderService,
        ShopifyTransform $productTransform,
        IRecommendationProcess $recommendationProcess,
        UpdateDefaultRecommendation $updateDefaultRecommendation,
        IShopRecommendationQuery $shopRecommendationQuery,
    ): void {
        $this->productQuery = $productQuery;
        $this->shopQuery = $shopQuery;
        $this->shopRecommendationCommand = $shopRecommendationCommand;
        $this->productRecommendationService = $productRecommendationService;
        $this->recommendationApiService = $recommendationApiService;
        $this->jobRecommendationCommand = $jobRecommendationCommand;
        $this->emailSenderService = $emailSenderService;
        $this->productTransform = $productTransform;
        $this->recommendationProcess = $recommendationProcess;
        $this->updateDefaultRecommendation = $updateDefaultRecommendation;
        $this->shopRecommendationQuery = $shopRecommendationQuery;

        $this->setContext();
    }

    /**
     * Check if default recommendation only (refresh count = 0)
     *
     * @param string $shopId
     * @return bool
     */
    private function isDefaultRecommendationOnly(string $shopId): bool
    {
        return $this->shopRecommendationQuery->getByShopId($shopId)->getRefreshCount() === 0;
    }

    /**
     * Handle install data and retry if failure
     *
     * @param callable $callback
     * @return void
     */
    private function retry(callable $callback): void
    {
        $retry = 0;
        do {
            try {
                $callback();
                return;
            } catch (Exception $e) {
                $retry++;
                Log::warning("Retry $retry failed for shop: $this->domain", ['exception' => $e]);
                sleep(self::DELAY_RECOMMEND_PROCESS);
            }
        } while ($retry < self::MAX_RETRY_PROCESS);
    }

    /**
     * Get request data for recommendation api
     *
     * @return ShopProductRecommendationRequestDTO
     */
    private function getRequestData(): ShopProductRecommendationRequestDTO
    {
        return $this->mergeData();
    }

    /**
     * Get order data to install
     *
     * @param string $domain
     * @return array
     */
    private function getOrdersData(string $domain): array
    {
        return $this->recommendationProcess->processOrderData($domain);
    }

    /**
     * Get data to request recommendation api
     *
     * @return ShopProductRecommendationRequestDTO
     */
    private function mergeData(): ShopProductRecommendationRequestDTO
    {
        $total = data_get($this->ordersData, 'total');
        $typeScores = data_get($this->ordersData, 'type_scores');
        $productScores = data_get($this->ordersData, 'product_scores');

        return new ShopProductRecommendationRequestDTO(
            self::MAX_RECOMMENDATION_PRODUCTS,
            $this->productsData,
            $typeScores,
            $total,
            $productScores,
        );
    }

    /**
     * Get map id with key gid
     *
     * @param string $shopId
     * @return array
     */
    private function getMapIdWithKeyGid(string $shopId): array
    {
        return $this->productQuery->getMapIdWithKeyGidByShopId($shopId);
    }

    /**
     * Create pending job.
     *
     * @param string $shopId
     * @return JobRecommendationCollection
     */
    private function createPendingJob(string $shopId): JobRecommendationCollection
    {
        $job = $this->jobRecommendationCommand->create(
            $shopId,
            JobRecommendationStatus::PENDING,
            0,
        );
        $this->shopRecommendationCommand->updateLastJobRecommendation($shopId, $job->getId());

        return $job;
    }

    /**
     * Update default recommendation
     *
     * @param ShopProductRecommendationRequestDTO $dataRequest
     * @param array $gidToIdMap
     * @return void
     */
    private function updateDefaultRecommendation(
        ShopProductRecommendationRequestDTO $dataRequest,
        array $gidToIdMap,
    ): void {
        call_user_func(
            $this->updateDefaultRecommendation,
            $dataRequest,
            $gidToIdMap,
        );
    }

    /**
     * Update job recommendation status.
     *
     * @param string $jobId
     * @param JobRecommendationStatus $status
     * @param array $data
     * @return void
     */
    private function updateJobStatus(string $jobId, JobRecommendationStatus $status, array $data): void
    {
        $this->jobRecommendationCommand->update($jobId, $status, $data);
    }

    /**
     * Handle exceptions during job processing.
     *
     * @param string $jobId
     * @param string $shopId
     * @param string $shopDomain
     * @return void
     */
    private function handleJobException(string $jobId, string $shopId, string $shopDomain): void
    {
        $this->updateJobStatus($jobId, JobRecommendationStatus::FAILED, ['error' => 'Error processing job.']);
        $this->sendEmail(JobRecommendationStatus::FAILED, $shopId, $shopDomain);
    }

    /**
     * Send email notification.
     *
     * @param JobRecommendationStatus $status
     * @param string $shopId
     * @param string $shopDomain
     * @return void
     */
    private function sendEmail(JobRecommendationStatus $status, string $shopId, string $shopDomain): void
    {
        $this->emailSenderService->sendRecommendationEmail($shopId, $shopDomain, $status);
    }

    /**
     * Set user context
     */
    private function setContext(): void
    {
        $userContext = app(UserContext::class);
        $shopSession = User::query()->where('name', $this->domain)->first();
        $userContext->setUser($shopSession);
    }
}
