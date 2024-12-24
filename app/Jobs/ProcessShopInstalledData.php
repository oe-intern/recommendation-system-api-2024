<?php

namespace App\Jobs;

use App\Contracts\Commands\IJobRecommendationCommand;
use App\Collections\JobRecommendationCollection;
use App\Contracts\Commands\IShopRecommendationCommand;
use App\Contracts\Mail\IEmailSender;
use App\Contracts\ModelRecommendation\IRecommendationApi;
use App\Contracts\Recommendation\IProductRecommendation;
use App\DTO\Payload\ShopProductRecommendationRequestDTO;
use App\DTO\Service\JobRecommendationResponse;
use App\Objects\Enums\JobRecommendationStatus;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class ProcessShopInstalledData implements ShouldQueue
{
    use Queueable, Dispatchable;

    /**
     * @var int
     */
    private const MAX_RETRY_PROCESS = 20;

    /**
     * @var int
     */
    private const DELAY_RECOMMEND_PROCESS = 60; // 1 minute

    /**
     * @var int
     */
    private const MAX_RETRY_GET_RESULT = 3;

    /**
     * @var int
     */
    private const DELAY_GET_RESULT = 10; // 10 seconds

    /**
     * @var array
     */
    protected array $gidToIdMap;

    /**
     * @var array
     */
    protected array $products;

    /**
     * @var string
     */
    protected string $shopId;

    /**
     * @var string
     */
    protected string $shopDomain;

    /**
     * @var ShopProductRecommendationRequestDTO
     */
    protected ShopProductRecommendationRequestDTO $data;

    /**
     * @var IRecommendationApi
     */
    private IRecommendationApi $recommendationApiService;

    /**
     * @var IProductRecommendation
     */
    private IProductRecommendation $productRecommendationService;

    /**
     * @var IJobRecommendationCommand
     */
    private IJobRecommendationCommand $jobRecommendationCommand;

    /**
     * @var IShopRecommendationCommand
     */
    private IShopRecommendationCommand $shopRecommendationCommand;

    /**
     * @var IEmailSender
     */
    private IEmailSender $emailSenderService;

    /**
     * Create a new job instance.
     *
     * @param array $gidToIdMap
     * @param array $products
     * @param string $shopId
     * @param string $shopDomain
     * @param ShopProductRecommendationRequestDTO $data
     */
    public function __construct(array $gidToIdMap, array $products, string $shopId, string $shopDomain, ShopProductRecommendationRequestDTO $data)
    {
        $this->gidToIdMap = $gidToIdMap;
        $this->products = $products;
        $this->shopId = $shopId;
        $this->shopDomain = $shopDomain;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @param IRecommendationApi $recommendationApiService
     * @param IProductRecommendation $productRecommendationService
     * @param IJobRecommendationCommand $jobRecommendationCommand
     * @param IShopRecommendationCommand $shopRecommendationCommand
     * @param IEmailSender $emailSenderService
     * @return void
     */
    public function handle(
        IRecommendationApi $recommendationApiService,
        IProductRecommendation $productRecommendationService,
        IJobRecommendationCommand $jobRecommendationCommand,
        IShopRecommendationCommand $shopRecommendationCommand,
        IEmailSender $emailSenderService,
    ): void {
        $this->initializeServices(
            $recommendationApiService,
            $productRecommendationService,
            $jobRecommendationCommand,
            $shopRecommendationCommand,
            $emailSenderService,
        );

        $job = $this->createPendingJob();

        try {
            $recommendationData = $this->fetchRecommendationData();

            if ($this->isRecommendationFailed($recommendationData)) {
                $this->updateFailedJob($job->getId(), 'Recommendation failed.');
                return;
            }

            $this->updateJobStatus($job->getId(), $recommendationData->status, $recommendationData->toArray());
            $this->processJobRecommendation($job->getId(), $recommendationData->jobId);
        } catch (Exception $e) {
            $this->handleJobException($job->getId(), $e);
        }
    }

    /**
     * Initialize services.
     *
     * @param IRecommendationApi $recommendationApiService
     * @param IProductRecommendation $productRecommendationService
     * @param IJobRecommendationCommand $jobRecommendationCommand
     * @param IShopRecommendationCommand $shopRecommendationCommand
     * @param IEmailSender $emailSenderService
     * @return void
     */
    private function initializeServices(
        IRecommendationApi $recommendationApiService,
        IProductRecommendation $productRecommendationService,
        IJobRecommendationCommand $jobRecommendationCommand,
        IShopRecommendationCommand $shopRecommendationCommand,
        IEmailSender $emailSenderService,
    ): void {
        $this->recommendationApiService = $recommendationApiService;
        $this->productRecommendationService = $productRecommendationService;
        $this->jobRecommendationCommand = $jobRecommendationCommand;
        $this->shopRecommendationCommand = $shopRecommendationCommand;
        $this->emailSenderService = $emailSenderService;
    }

    /**
     * Create pending job.
     *
     * @return JobRecommendationCollection
     */
    private function createPendingJob(): JobRecommendationCollection
    {
        $job = $this->jobRecommendationCommand->create(
            $this->shopId,
            JobRecommendationStatus::PENDING,
            0,
        );
        $this->shopRecommendationCommand->updateLastJobRecommendation($this->shopId, $job->getId());

        return $job;
    }

    /**
     * Fetch recommendation data.
     *
     * @return JobRecommendationResponse
     * @throws Exception
     */
    private function fetchRecommendationData(): JobRecommendationResponse
    {
        return $this->recommendationApiService->recommend($this->data);
    }

    /**
     * Check if request recommendation is failed.
     *
     * @param $recommendationData
     * @return bool
     */
    private function isRecommendationFailed($recommendationData): bool
    {
        return $recommendationData->isRevoked() || $recommendationData->isFailed();
    }

    /**
     * Update job failed recommendation status.
     *
     * @param string $jobId
     * @param mixed $data
     * @return void
     */
    private function updateFailedJob(string $jobId, mixed $data): void
    {
        $this->jobRecommendationCommand->update($jobId, JobRecommendationStatus::FAILED, ['error' => $data]);
        $this->sendEmail(JobRecommendationStatus::FAILED, $this->shopId, $this->shopDomain);
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
     * Process job recommendation.
     *
     * @param string $jobCollectionId
     * @param string $recommendationJobId
     * @return void
     */
    private function processJobRecommendation(string $jobCollectionId, string $recommendationJobId): void
    {
        $retryRecommendProcess = 0;

        do {
            if ($this->attemptProcessJobRecommendation($jobCollectionId, $recommendationJobId)) {
                return;
            }

            $retryRecommendProcess++;
            sleep(self::DELAY_RECOMMEND_PROCESS);
        } while ($retryRecommendProcess < self::MAX_RETRY_PROCESS);

        $this->updateFailedJob($jobCollectionId, 'Job recommendation process timed out after retries.');
    }

    /**
     * Attempt to process job recommendation.
     *
     * @param string $jobCollectionId
     * @param string $recommendationJobId
     * @return bool
     */
    private function attemptProcessJobRecommendation(string $jobCollectionId, string $recommendationJobId): bool
    {
        try {
            $jobResponse = $this->recommendationApiService->getJobRecommendation($recommendationJobId);

            if ($this->isJobResponseFailed($jobResponse)) {
                $this->updateFailedJob($jobCollectionId, $jobResponse->errorMessage);
                return true;
            }

            if ($jobResponse->isSuccessful()) {
                $this->handleSuccessfulJob($jobCollectionId, $jobResponse);
                return true;
            }
        } catch (Exception $e) {
            Log::error('Error processing job recommendation.', ['error' => $e->getMessage()]);
        }

        return false;
    }

    /**
     * Check if job response is failed.
     *
     * @param $jobResponse
     * @return bool
     */
    private function isJobResponseFailed($jobResponse): bool
    {
        return $jobResponse->isFailed() || $jobResponse->isRevoked();
    }

    /**
     * Handle successful job.
     *
     * @param string $jobCollectionId
     * @param $jobResponse
     * @return void
     */
    private function handleSuccessfulJob(string $jobCollectionId, $jobResponse): void
    {
        try {
            $recommendationData = $this->recommendationApiService->getJobRecommendationResult($jobResponse->resultUrl);
            $this->updateCompletedJob($recommendationData, $jobCollectionId, $jobResponse->toArray());

        } catch (Exception $e) {
            $this->updateFailedJob($jobCollectionId, 'Failed to fetch recommendation result after retries.');
        }
    }

    /**
     * Update job completed recommendation status.
     *
     * @param array $recommendationData
     * @param string $jobId
     * @param mixed $result
     * @return void
     */
    private function updateCompletedJob(array $recommendationData, string $jobId, array $result): void
    {
        $this->productRecommendationService->updateManyRecommendation(
            $recommendationData,
            $this->gidToIdMap,
        );
        $this->jobRecommendationCommand->update($jobId, JobRecommendationStatus::SUCCESS, $result);
        $this->shopRecommendationCommand->decreaseRefreshRecommendation($this->shopId);
        $this->sendEmail(JobRecommendationStatus::SUCCESS, $this->shopId, $this->shopDomain);
    }

    /**
     * Handle exceptions during job processing.
     *
     * @param string $jobId
     * @param Exception $e
     * @return void
     */
    private function handleJobException(string $jobId, Exception $e): void
    {
        $this->updateJobStatus($jobId, JobRecommendationStatus::FAILED, ['error' => $e->getMessage()]);
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
}
