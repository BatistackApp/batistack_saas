<?php

namespace App\Models\Commerce;

use App\Enums\Commerce\InvoiceStatus;
use App\Enums\Commerce\InvoiceType;
use App\Models\Core\Tenants;
use App\Models\Projects\Project;
use App\Models\Tiers\Tiers;
use App\Observers\Commerce\InvoicesObserver;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([InvoicesObserver::class])]
class Invoices extends Model
{
    use HasFactory, SoftDeletes, HasTenant;

    protected $guarded = [];

    public function tiers(): BelongsTo
    {
        return $this->belongsTo(Tiers::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    protected function casts(): array
    {
        return [
            'is_autoliquidation' => 'boolean',
            'due_date' => 'date',
            'type' => InvoiceType::class,
            'status' => InvoiceStatus::class,
        ];
    }

    /**
     * Calcule le montant Net à Payer.
     * Formule : Total TTC - Retenue de Garantie - Compte de Prorata.
     */
    public function getNetToPayAttribute(): float
    {
        return (float) (
            (float) $this->total_ttc -
            (float) $this->retenue_garantie_amount -
            (float) $this->compte_prorata_amount
        );
    }
}
