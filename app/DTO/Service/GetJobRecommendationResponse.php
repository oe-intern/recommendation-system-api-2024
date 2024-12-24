<?php

namespace App\DTO\Service;

use App\Objects\Enums\JobRecommendationStatus;

class GetJobRecommendationResponse
{
    /**
     * Job ID.
     *
     * @var string
     */
    public string $jobId;

    /**
     * Job status.
     *
     * @var JobRecommendationStatus
     */
    public JobRecommendationStatus $status;

    /**
     * Result url of data.
     *
     * @var string
     */
    public string $resultUrl;

    /**
     * Error message.
     *
     * @var mixed
     */
    public mixed $errorMessage;

    /**
     * JobRecommendationResponseDTO constructor.
     *
     * @param string $jobId
     * @param string $status
     * @param string|null $resultUrl
     * @param mixed $errorMessage
     */
    public function __construct(string $jobId, string $status, ?string $resultUrl, mixed $errorMessage)
    {
        $this->jobId = $jobId;
        $this->status = JobRecommendationStatus::from($status);
        $this->resultUrl = $resultUrl;
        $this->errorMessage = $errorMessage;
    }

    /**
     * Convert DTO to array for API response.
     */
    public function toArray(): array
    {
        return [
            'job_id' => $this->jobId,
            'status' => $this->status,
            'result_url' => $this->resultUrl,
            'error_message' => $this->errorMessage,
        ];
    }

    /**
     * Check if the job is successful.
     */
    public function isSuccessful(): bool
    {
        return $this->status->equals(JobRecommendationStatus::SUCCESS);
    }

    /**
     * Check if the job is failed.
     */
    public function isFailed(): bool
    {
        return $this->status->equals(JobRecommendationStatus::FAILED);
    }

    /**
     * Check if the job is revoked.
     */
    public function isRevoked(): bool
    {
        return $this->status->equals(JobRecommendationStatus::REVOKED);
    }
}
