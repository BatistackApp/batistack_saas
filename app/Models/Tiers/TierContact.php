<?php

namespace App\Models\Tiers;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TierContact extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function tier(): BelongsTo
    {
        return $this->belongsTo(Tiers::class, 'tiers_id');
    }

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    protected function fullName(): Attribute
    {
        return Attribute::get(fn () => "{$this->first_name} ".mb_strtoupper($this->last_name));
    }
}
