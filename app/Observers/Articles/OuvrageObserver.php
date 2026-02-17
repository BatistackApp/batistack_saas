<?php

namespace App\Observers\Articles;

use App\Models\Articles\Ouvrage;
use Illuminate\Support\Str;

class OuvrageObserver
{
    public function creating(Ouvrage $ouvrage): void
    {
        if (empty($ouvrage->sku)) {
            $ouvrage->sku = 'OUV-'.strtoupper(Str::random(8));
        }
    }
}
