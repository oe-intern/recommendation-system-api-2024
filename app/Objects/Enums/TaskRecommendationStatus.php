<?php

namespace App\Objects\Enums;

enum TaskRecommendationStatus: string
{
    case PENDING = 'PENDING';
    case STARTED = 'STARTED';
    case SUCCESS = 'SUCCESS';
    case FAILED = 'FAILED';
    case RETRY = 'RETRY';
    case REVOKED = 'REVOKED';
}
