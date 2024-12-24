<?php

namespace App\Storage\Commands;

use App\Collections\JobRecommendationCollection;
use App\Contracts\Commands\IJobRecommendationCommand;
use App\Contracts\Queries\IJobRecommendationQuery;
use App\Objects\Enums\JobRecommendationStatus;

class JobRecommendationCommand implements IJobRecommendationCommand
{
    /**
     * @var IJobRecommendationQuery
     */
    protected IJobRecommendationQuery $jobRecommendationQuery;

    /**
     * JobRecommendationCommand constructor.
     *
     * @param IJobRecommendationQuery $jobRecommendationQuery
     */
    public function __construct(IJobRecommendationQuery $jobRecommendationQuery)
    {
        $this->jobRecommendationQuery = $jobRecommendationQuery;
    }

    /**
     * Create a new job recommendation.
     *
     * @param string $shopId
     * @param JobRecommendationStatus $status
     * @param int|null $retryCount
     * @return JobRecommendationCollection
     */
    public function create(
        string $shopId,
        JobRecommendationStatus $status,
        ?int $retryCount,
    ): JobRecommendationCollection {
        return JobRecommendationCollection::query()
            ->create([
                'shop_id' => $shopId,
                'status' => $status->value,
                'retry_count' => $retryCount ?? 0,
            ]);
    }

    /**
     * Increment the retry count of the job recommendation.
     *
     * @param string $jobId
     * @return JobRecommendationCollection
     */
    public function incrementRetryCount(string $jobId): JobRecommendationCollection
    {
        $jobRecommendation = $this->jobRecommendationQuery->getById($jobId);
        $jobRecommendation->update(
            ['retry_count' => $jobRecommendation->getRetryCount() + 1],
        );

        return $jobRecommendation;
    }

    /**
     * Update the job recommendation.
     *
     * @param string $jobId
     * @param JobRecommendationStatus $status
     * @param array|null $result
     * @return bool
     */
    public function update(
        string $jobId,
        JobRecommendationStatus $status,
        ?array $result,
    ): bool {
        return $this->jobRecommendationQuery
            ->getById($jobId)
            ->update([
                'status' => $status->value,
                'result' => $result,
            ]);
    }
}
