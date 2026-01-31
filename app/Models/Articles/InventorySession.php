<?php

namespace App\Models\Articles;

use App\Enums\Articles\InventorySessionStatus;
use App\Models\Core\Tenants;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventorySession extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenants::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InventoryLine::class);
    }

    protected function casts(): array
    {
        return [
            'status' => InventorySessionStatus::class,
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'validated_at' => 'datetime',
        ];
    }
}
