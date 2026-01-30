<?php

namespace App\Models\Tiers;

use App\Enums\Tiers\TierDocumentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TierDocument extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status' => TierDocumentStatus::class,
            'expires_at' => 'date',
        ];
    }

    public function tiers(): BelongsTo
    {
        return $this->belongsTo(Tiers::class);
    }
}
