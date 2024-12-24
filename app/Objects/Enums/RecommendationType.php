<?php

namespace App\Objects\Enums;

enum RecommendationType: string
{
    // The default recommendation type.
    case DEFAULT = 'DEFAULT';
    // Customer recommendation type.
    case AUTO = 'AUTO';
    // System recommendation type.
    case MANUAL = 'MANUAL';
}
