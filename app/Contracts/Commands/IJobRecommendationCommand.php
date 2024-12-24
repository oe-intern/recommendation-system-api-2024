<?php

namespace App\Contracts\Commands;

use App\Collections\JobRecommendationCollection;
use App\Objects\Enums\JobRecommendationStatus;

interface  IJobRecommendationCommand
{
    /**
     * Create a new job recommendation.
     *
     * @param string $shopId
     * @param JobRecommendationStatus $status
     * @param int|null $retryCount
     *
     * @return JobRecommendationCollection
     */
    public function create(
        string $shopId,
        JobRecommendationStatus $status,
        ?int $retryCount,
    ): JobRecommendationCollection;

    /**
     * Update the job recommendation by job id.
     *
     * @param string $jobId
     * @param JobRecommendationStatus $status
     * @param array|null $result
     *
     * @return bool
     */
    public function update(
        string $jobId,
        JobRecommendationStatus $status,
        ?array $result,
    ): bool;

    /**
     * Increment the retry count of the job recommendation by job id.
     *
     * @param string $jobId
     * @return JobRecommendationCollection
     */
    public function incrementRetryCount(string $jobId): JobRecommendationCollection;
}
