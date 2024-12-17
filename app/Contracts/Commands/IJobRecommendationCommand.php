<?php

namespace App\Contracts\Commands;

use App\Collections\JobRecommendationCollection;
use App\Objects\Enums\JobRecommendationStatus;

interface  IJobRecommendationCommand
{
    /**
     * Create a new job recommendation.
     *
     * @param string $shop_id
     * @param JobRecommendationStatus $status
     * @param int|null $retry_count
     *
     * @return JobRecommendationCollection
     */
    public function create(
        string $shop_id,
        JobRecommendationStatus $status,
        ?int $retry_count,
    ): JobRecommendationCollection;

    /**
     * Update the job recommendation by job id.
     *
     * @param string $job_id
     * @param JobRecommendationStatus $status
     * @param array|null $result
     *
     * @return bool
     */
    public function update(
        string $job_id,
        JobRecommendationStatus $status,
        ?array $result,
    ): bool;

    /**
     * Increment the retry count of the job recommendation by job id.
     *
     * @param string $job_id
     * @return JobRecommendationCollection
     */
    public function incrementRetryCount(string $job_id): JobRecommendationCollection;
}
