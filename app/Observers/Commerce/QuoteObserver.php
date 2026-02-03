<?php

namespace App\Observers\Commerce;

use App\Models\Commerce\Quote;

class QuoteObserver
{
    public function creating(Quote $quote): void
    {
        if (empty($quote->reference)) {
            $year = date('Y');
            $count = Quote::whereYear('created_at', $year)->count() + 1;
            $quote->reference = "DEV-{$year}-".str_pad($count, 5, '0', STR_PAD_LEFT);
        }
    }
}
