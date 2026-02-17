<?php

namespace App\Models\Commerce;

use App\Enums\Commerce\PurchaseOrderStatus;
use App\Models\Projects\Project;
use App\Models\Tiers\Tiers;
use App\Models\User;
use App\Observers\Commerce\PurchaseOrderObserver;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([PurchaseOrderObserver::class])]
class PurchaseOrder extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $guarded = [];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Tiers::class, 'supplier_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'expected_delivery_date' => 'date',
            'status' => PurchaseOrderStatus::class,
        ];
    }
}
