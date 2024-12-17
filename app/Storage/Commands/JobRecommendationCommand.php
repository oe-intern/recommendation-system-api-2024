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
    protected IJobRecommendationQuery $job_recommendation_query;

    /**
     * JobRecommendationCommand constructor.
     *
     * @param IJobRecommendationQuery $job_recommendation_query
     */
    public function __construct(IJobRecommendationQuery $job_recommendation_query)
    {
        $this->job_recommendation_query = $job_recommendation_query;
    }

    /**
     * Create a new job recommendation.
     *
     * @param string $shop_id
     * @param JobRecommendationStatus $status
     * @param int|null $retry_count
     * @return JobRecommendationCollection
     */
    public function create(
        string $shop_id,
        JobRecommendationStatus $status,
        ?int $retry_count,
    ): JobRecommendationCollection {
        return JobRecommendationCollection::query()
            ->create([
                'shop_id' => $shop_id,
                'status' => $status->value,
                'retry_count' => $retry_count ?? 0,
            ]);
    }

    /**
     * Increment the retry count of the job recommendation.
     *
     * @param string $job_id
     * @return JobRecommendationCollection
     */
    public function incrementRetryCount(string $job_id): JobRecommendationCollection
    {
        $job_recommendation = $this->job_recommendation_query->getById($job_id);
        $job_recommendation->update(
            ['retry_count' => $job_recommendation->getRetryCount() + 1],
        );

        return $job_recommendation;
    }

    /**
     * Update the job recommendation.
     *
     * @param string $job_id
     * @param JobRecommendationStatus $status
     * @param array|null $result
     * @return bool
     */
    public function update(
        string $job_id,
        JobRecommendationStatus $status,
        ?array $result,
    ): bool {
        return $this->job_recommendation_query
            ->getById($job_id)
            ->update([
                'status' => $status->value,
                'result' => $result,
            ]);
    }
}
