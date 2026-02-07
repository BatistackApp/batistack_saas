<?php

namespace App\Enums\Locations;

enum RentalBillingCycle: string
{
    case DAY = 'day';
    case WEEK = 'week';
    case MONTH = 'month';
}
