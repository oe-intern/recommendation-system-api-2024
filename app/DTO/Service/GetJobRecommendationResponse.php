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
    public string $job_id;

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
    public string $result_url;

    /**
     * Error message.
     *
     * @var mixed
     */
    public mixed $error_message;

    /**
     * JobRecommendationResponseDTO constructor.
     *
     * @param string $job_id
     * @param string $status
     * @param string|null $result_url
     * @param mixed $error_message
     */
    public function __construct(string $job_id, string $status, ?string $result_url, mixed $error_message)
    {
        $this->job_id = $job_id;
        $this->status = JobRecommendationStatus::from($status);
        $this->result_url = $result_url;
        $this->error_message = $error_message;
    }

    /**
     * Convert DTO to array for API response.
     */
    public function toArray(): array
    {
        return [
            'job_id' => $this->job_id,
            'status' => $this->status,
            'result_url' => $this->result_url,
            'error_message' => $this->error_message,
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
