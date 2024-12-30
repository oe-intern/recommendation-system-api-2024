<?php

namespace App\DTO\Response;

use App\Collections\JobRecommendationCollection;
use Illuminate\Contracts\Support\Arrayable;

readonly class GetJobProcessingStatusResponse implements Arrayable
{
    /**
     * GetJobProcessingStatusResponse constructor.
     *
     * @param JobRecommendationCollection $jobRecommendationCollection
     */
    public function __construct(
        private JobRecommendationCollection $jobRecommendationCollection,
    ) {}

    /**
     * Convert data to array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'status' => $this->jobRecommendationCollection->getStatus(),
        ];
    }
}
