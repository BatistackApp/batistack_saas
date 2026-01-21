<?php

namespace App\Models\Commerce;

use App\Enums\Commerce\DocumentStatus;
use App\Models\Core\Tenant;
use App\Observers\Commerce\AvenantObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([AvenantObserver::class])]
class Avenant extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function commande(): BelongsTo
    {
        return $this->belongsTo(Commande::class);
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(AvenantLigne::class);
    }

    protected function casts(): array
    {
        return [
            'date_avenant' => 'date',
            'montant_ht' => 'decimal:2',
            'montant_tva' => 'decimal:2',
            'montant_ttc' => 'decimal:2',
            'status' => DocumentStatus::class,
        ];
    }
}
