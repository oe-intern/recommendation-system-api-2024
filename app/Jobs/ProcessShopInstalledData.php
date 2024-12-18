<?php

namespace App\Jobs;

use App\Contracts\Commands\IJobRecommendationCommand;
use App\Collections\JobRecommendationCollection;
use App\Contracts\Commands\IShopCommand;
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
    protected array $map_gid_id;

    /**
     * @var array
     */
    protected array $products;

    /**
     * @var string
     */
    protected string $shop_id;

    /**
     * @var ShopProductRecommendationRequestDTO
     */
    protected ShopProductRecommendationRequestDTO $data;

    /**
     * @var IRecommendationApi
     */
    private IRecommendationApi $recommendation_api_service;

    /**
     * @var IProductRecommendation
     */
    private IProductRecommendation $product_recommendation_service;

    /**
     * @var IJobRecommendationCommand
     */
    private IJobRecommendationCommand $job_recommendation_command;

    /**
     * @var IShopCommand
     */
    private IShopCommand $shop_command;

    /**
     * Create a new job instance.
     *
     * @param array $map_gid_id
     * @param array $products
     * @param string $shop_id
     * @param ShopProductRecommendationRequestDTO $data
     */
    public function __construct(array $map_gid_id, array $products, string $shop_id, ShopProductRecommendationRequestDTO $data)
    {
        $this->map_gid_id = $map_gid_id;
        $this->products = $products;
        $this->shop_id = $shop_id;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @param IRecommendationApi $recommendation_api_service
     * @param IProductRecommendation $product_recommendation_service
     * @param IJobRecommendationCommand $job_recommendation_command
     * @param IShopCommand $shop_command
     * @return void
     */
    public function handle(
        IRecommendationApi $recommendation_api_service,
        IProductRecommendation $product_recommendation_service,
        IJobRecommendationCommand $job_recommendation_command,
        IShopCommand $shop_command,
    ): void {
        $this->initializeServices(
            $recommendation_api_service,
            $product_recommendation_service,
            $job_recommendation_command,
            $shop_command,
        );

        $job = $this->createPendingJob();

        try {
            $recommendation_data = $this->fetchRecommendationData();

            if ($this->isRecommendationFailed($recommendation_data)) {
                $this->updateFailedJob($job->getId(), 'Recommendation failed.');
                return;
            }

            $this->updateJobStatus($job->getId(), $recommendation_data->status, $recommendation_data->toArray());
            $this->processJobRecommendation($job->getId(), $recommendation_data->job_id);
        } catch (Exception $e) {
            $this->handleJobException($job->getId(), $e);
        }
    }

    /**
     * Initialize services.
     *
     * @param IRecommendationApi $recommendation_api_service
     * @param IProductRecommendation $product_recommendation_service
     * @param IJobRecommendationCommand $job_recommendation_command
     * @param IShopCommand $shop_command
     * @return void
     */
    private function initializeServices(
        IRecommendationApi $recommendation_api_service,
        IProductRecommendation $product_recommendation_service,
        IJobRecommendationCommand $job_recommendation_command,
        IShopCommand $shop_command,
    ): void {
        $this->recommendation_api_service = $recommendation_api_service;
        $this->product_recommendation_service = $product_recommendation_service;
        $this->job_recommendation_command = $job_recommendation_command;
        $this->shop_command = $shop_command;
    }

    /**
     * Create pending job.
     *
     * @return JobRecommendationCollection
     */
    private function createPendingJob(): JobRecommendationCollection
    {
        $job = $this->job_recommendation_command->create(
            $this->shop_id,
            JobRecommendationStatus::PENDING,
            0,
        );
        $this->shop_command->updateLastJobRecommendation($this->shop_id, $job->getId());

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
        return $this->recommendation_api_service->recommend($this->data);
    }

    /**
     * Check if request recommendation is failed.
     *
     * @param $recommendation_data
     * @return bool
     */
    private function isRecommendationFailed($recommendation_data): bool
    {
        return $recommendation_data->isRevoked() || $recommendation_data->isFailed();
    }

    /**
     * Update job failed recommendation status.
     *
     * @param string $job_id
     * @param mixed $data
     * @return void
     */
    private function updateFailedJob(string $job_id, mixed $data): void
    {
        $this->job_recommendation_command->update($job_id, JobRecommendationStatus::FAILED, ['error' => $data]);
    }

    /**
     * Update job recommendation status.
     *
     * @param string $job_id
     * @param JobRecommendationStatus $status
     * @param array $data
     * @return void
     */
    private function updateJobStatus(string $job_id, JobRecommendationStatus $status, array $data): void
    {
        $this->job_recommendation_command->update($job_id, $status, $data);
    }

    /**
     * Process job recommendation.
     *
     * @param string $job_collection_id
     * @param string $job_recommendation_id
     * @return void
     */
    private function processJobRecommendation(string $job_collection_id, string $job_recommendation_id): void
    {
        $retry_recommend_process = 0;

        do {
            if ($this->attemptProcessJobRecommendation($job_collection_id, $job_recommendation_id)) {
                return;
            }

            $retry_recommend_process++;
            sleep(self::DELAY_RECOMMEND_PROCESS);
        } while ($retry_recommend_process < self::MAX_RETRY_PROCESS);

        $this->updateFailedJob($job_collection_id, 'Job recommendation process timed out after retries.');
    }

    /**
     * Attempt to process job recommendation.
     *
     * @param string $job_collection_id
     * @param string $job_recommendation_id
     * @return bool
     */
    private function attemptProcessJobRecommendation(string $job_collection_id, string $job_recommendation_id): bool
    {
        try {
            $job_response = $this->recommendation_api_service->getJobRecommendation($job_recommendation_id);

            if ($this->isJobResponseFailed($job_response)) {
                $this->updateFailedJob($job_collection_id, $job_response->error_message);
                return true;
            }

            if ($job_response->isSuccessful()) {
                $this->handleSuccessfulJob($job_collection_id, $job_response);
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
     * @param $job_response
     * @return bool
     */
    private function isJobResponseFailed($job_response): bool
    {
        return $job_response->isFailed() || $job_response->isRevoked();
    }

    /**
     * Handle successful job.
     *
     * @param string $job_collection_id
     * @param $job_response
     * @return void
     */
    private function handleSuccessfulJob(string $job_collection_id, $job_response): void
    {
        try {
            $recommendation_data = $this->recommendation_api_service->getJobRecommendationResult($job_response->result_url);
            $this->updateCompletedJob($recommendation_data, $job_collection_id, $job_response->toArray());

        } catch (Exception $e) {
            $this->updateFailedJob($job_collection_id, 'Failed to fetch recommendation result after retries.');
        }
    }

    /**
     * Update job completed recommendation status.
     *
     * @param array $recommendation_data
     * @param string $job_id
     * @param mixed $result
     * @return void
     */
    private function updateCompletedJob(array $recommendation_data, string $job_id, array $result): void
    {
        $this->product_recommendation_service->updateManyRecommendation(
            $recommendation_data,
            $this->map_gid_id,
        );
        $this->job_recommendation_command->update($job_id, JobRecommendationStatus::SUCCESS, $result);
    }

    /**
     * Handle exceptions during job processing.
     *
     * @param string $job_id
     * @param Exception $e
     * @return void
     */
    private function handleJobException(string $job_id, Exception $e): void
    {
        $this->updateJobStatus($job_id, JobRecommendationStatus::FAILED, ['error' => $e->getMessage()]);
    }
}
