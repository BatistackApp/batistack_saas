<?php

namespace App\Models\Tiers;

use App\Enums\Tiers\TierStatus;
use App\Models\Core\Tenants;
use App\Models\User;
use App\Observers\Tiers\TierObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[ObservedBy([TierObserver::class])]
class Tiers extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status' => TierStatus::class,
            'metadata' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenants::class);
    }

    public function types(): HasMany
    {
        return $this->hasMany(TierType::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // Accessors
    public function getDisplayNameAttribute(): string
    {
        return $this->type_entite === 'personne_morale'
            ? $this->raison_sociale
            : "{$this->prenom} {$this->nom}";
    }

    // Methods
    public function hasType(TierType $type): bool
    {
        return $this->types()->where('type', $type->value)->exists();
    }

    public function addType(TierType $type, bool $isPrimary = false): void
    {
        if (! $this->hasType($type)) {
            $this->types()->create([
                'type' => $type->value,
                'is_primary' => $isPrimary,
            ]);
        }
    }

    private function generateCodeTiers(): string
    {
        $prefix = Str::upper(Str::random(3));
        $number = str_pad((string) (self::count() + 1), 6, '0', STR_PAD_LEFT);

        return "{$prefix}-{$number}";
    }
}
