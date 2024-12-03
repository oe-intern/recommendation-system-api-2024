<?php

namespace App\Objects\Enums;

enum  StatisticsGroupBy: string
{
    case HOUR = 'hour';
    case DAY = 'day';
    case MONTH = 'month';
    case YEAR = 'year';
}
