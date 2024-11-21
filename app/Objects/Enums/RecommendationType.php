<?php

namespace App\Objects\Enums;

enum RecommendationType: string
{
    // The default recommendation type.
    case DEFAULT = 'default';
    // Customer recommendation type.
    case AUTO = 'auto';
    // System recommendation type.
    case MANUAL = 'manual';
}
