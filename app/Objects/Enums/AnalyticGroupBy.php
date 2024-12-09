<?php

namespace App\Objects\Enums;

enum  AnalyticGroupBy: string
{
    case HOUR = 'hour';
    case DAY = 'day';
    case MONTH = 'month';
    case YEAR = 'year';
}
