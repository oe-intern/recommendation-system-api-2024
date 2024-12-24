<?php

namespace App\DTO\Service;

use App\Objects\Enums\JobRecommendationStatus;

class JobRecommendationResponse
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
     * JobRecommendationResponseDTO constructor.
     *
     * @param string $jobId
     * @param string $status
     */
    public function __construct(string $jobId, string $status)
    {
        $this->jobId = $jobId;
        $this->status = JobRecommendationStatus::from($status);
    }

    /**
     * Convert DTO to array for API response.
     */
    public function toArray(): array
    {
        return [
            'job_id' => $this->jobId,
            'status' => $this->status,
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
     * Check if the job is pending.
     */
    public function isPending(): bool
    {
        return $this->status->equals(JobRecommendationStatus::PENDING);
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

    /**
     * Check if the job is retry.
     */
    public function isRetry(): bool
    {
        return $this->status->equals(JobRecommendationStatus::RETRY);
    }

    /**
     * Check if the job is started.
     */
    public function isStarted(): bool
    {
        return $this->status->equals(JobRecommendationStatus::STARTED);
    }
}
