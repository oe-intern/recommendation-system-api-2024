<?php

namespace App\Jobs;

use App\Contracts\Commands\IJobRecommendationCommand;
use App\Contracts\ModelRecommendation\IRecommendationApi;
use App\Contracts\Recommendation\IProductRecommendation;
use App\DTO\Payload\ShopProductRecommendationRequestDTO;
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
     * Create a new job instance.
     *
     * @param array $map_gid_id
     * @param array $products
     * @param ShopProductRecommendationRequestDTO $data
     */
    public function __construct(array $map_gid_id, array $products, ShopProductRecommendationRequestDTO $data)
    {
        $this->map_gid_id = $map_gid_id;
        $this->products = $products;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @param IRecommendationApi $recommendation_api_service
     * @param IProductRecommendation $product_recommendation_service
     * @param IJobRecommendationCommand $job_recommendation_command
     * @return void
     *
     * @throws Exception
     */
    public function handle(
        IRecommendationApi $recommendation_api_service,
        IProductRecommendation $product_recommendation_service,
        IJobRecommendationCommand $job_recommendation_command,
    ): void {
        Log::info('ProcessShopInstalledData job started.');
        $this->recommendation_api_service = $recommendation_api_service;
        $this->product_recommendation_service = $product_recommendation_service;
        $this->job_recommendation_command = $job_recommendation_command;

        $job = $job_recommendation_command->create(
            'shop_id',
            JobRecommendationStatus::PENDING,
            0,
        );

        try {
            // Call api to handle recommendation
            $recommendation_data = $recommendation_api_service->recommend($this->data);
            // Check if recommendation is revoked or failed
            if ($recommendation_data->isRevoked() || $recommendation_data->isFailed()) {
                $this->updateFailedJob(
                    $job->getId(),
                    'Recommendation failed.',
                );
                return;
            }
            // Update job status and process recommendation
            $this->updateJobStatus($job->getId(), $recommendation_data->status, $recommendation_data->toArray());
            $this->processJobRecommendation($job->getId(), $recommendation_data->job_id);
        } catch (Exception $e) {
            $this->handleJobException($job->getId(), $e);
        }
    }

    /**
     * Update job failed recommendation status.
     *
     * @param int $job_id
     * @param mixed $data
     * @return void
     */
    private function updateFailedJob(int $job_id, mixed $data): void
    {
        $this->job_recommendation_command->update($job_id, JobRecommendationStatus::FAILED, ['error' => $data]);
    }

    /**
     * Update job recommendation status.
     *
     * @param int $job_id
     * @param JobRecommendationStatus $status
     * @param array $data
     * @return void
     */
    private function updateJobStatus(int $job_id, JobRecommendationStatus $status, array $data): void
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
            try {
                $job_response = $this->recommendation_api_service->getJobRecommendation($job_recommendation_id);

                if ($job_response->isFailed() || $job_response->isRevoked()) {
                    $this->updateFailedJob($job_collection_id, $job_response->error_message);
                    return;
                }

                if ($job_response->isSuccessful()) {
                    $result_url = $job_response->result_url;

                    try {
                        $recommendation_data = $this->recommendation_api_service->getJobRecommendationResult($result_url);
                        $this->updateCompletedJob(
                            $recommendation_data,
                            $job_collection_id,
                            $job_response->toArray(),
                        );
                        return;
                    } catch (Exception $e) {
                        Log::error('Error fetching recommendation result.', ['error' => $e->getMessage()]);
                    }

                    $this->updateFailedJob($job_collection_id, 'Failed to fetch recommendation result after retries.');
                    return;
                }

                sleep(self::DELAY_RECOMMEND_PROCESS);
            } catch (Exception $e) {
                Log::error('Error processing job recommendation.', ['error' => $e->getMessage()]);
                sleep(self::DELAY_RECOMMEND_PROCESS);
            }

            $retry_recommend_process++;
        } while ($retry_recommend_process < self::MAX_RETRY_PROCESS);

        $this->updateFailedJob($job_collection_id, 'Job recommendation process timed out after retries.');
    }

    /**
     * Update job completed recommendation status.
     *
     * @param array $recommendation_data
     * @param int $job_id
     * @param mixed $result
     * @return void
     */
    private function updateCompletedJob(array $recommendation_data, int $job_id, $result): void
    {
        $this->product_recommendation_service->updateManyRecommendation(
            $recommendation_data,
            $this->map_gid_id,
        );
        $this->job_recommendation_command->update($job_id, JobRecommendationStatus::SUCCESS, $result);
        Log::info('ProcessShopInstalledData job completed.');
    }

    /**
     * Handle exceptions during job processing.
     *
     * @param int $job_id
     * @param Exception $e
     * @return void
     */
    private function handleJobException(int $job_id, Exception $e): void
    {
        $this->updateJobStatus($job_id, JobRecommendationStatus::FAILED, ['error' => $e->getMessage()]);
        Log::error('ProcessShopInstalledData job failed.', ['error' => $e->getMessage()]);
    }
}
