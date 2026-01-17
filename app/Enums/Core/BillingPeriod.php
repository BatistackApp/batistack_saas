<?php

namespace App\Enums\Core;

enum BillingPeriod: string
{
    case Monthly = 'monthly';
    case Yearly = 'yearly';

    case Quarterly = 'quarterly';
    case OneTime = 'one-time';
}
