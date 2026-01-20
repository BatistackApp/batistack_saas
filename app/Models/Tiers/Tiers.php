<?php

namespace App\Models\Tiers;

use App\Enums\Tiers\TierType;
use App\Models\Core\Tenant;
use App\Observers\Tiers\TierObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

#[ObservedBy([TierObserver::class])]
class Tiers extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(TierAddress::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(TierContact::class);
    }

    protected function casts(): array
    {
        return [
            'types' => 'array',
            'is_active' => 'boolean',
            'discount_percentage' => 'decimal:2',
        ];
    }

    public function hasType(TierType $type): bool
    {
        return in_array($type->value, $this->types ?? []);
    }

    public function addType(TierType $type): void
    {
        $types = $this->types ?? [];
        if (! in_array($type->value, $types)) {
            $types[] = $type->value;
            $this->update(['types' => $types]);
        }
    }

    public function removeType(TierType $type): void
    {
        $types = $this->types ?? [];
        $this->update(['types' => array_filter($types, fn ($t) => $t !== $type->value)]);
    }
}
