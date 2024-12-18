<?php

namespace App\Objects\Enums;

enum JobRecommendationStatus: string
{
    case PENDING = 'PENDING';
    case STARTED = 'STARTED';
    case SUCCESS = 'SUCCESS';
    case FAILED = 'FAILED';
    case RETRY = 'RETRY';
    case REVOKED = 'REVOKED';

    /**
     * Check if the status is equal to the given status.
     *
     * @param JobRecommendationStatus $status
     * @return bool
     */
    public function equals(JobRecommendationStatus $status): bool
    {
        return $this->value === $status->value;
    }
}
