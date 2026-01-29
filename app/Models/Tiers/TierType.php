<?php

namespace App\Models\Tiers;

use App\Observers\Tiers\TierTypeObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([TierTypeObserver::class])]
class TierType extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function tiers(): BelongsTo
    {
        return $this->belongsTo(Tiers::class);
    }

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'type' => \App\Enums\Tiers\TierType::class,
        ];
    }
}
