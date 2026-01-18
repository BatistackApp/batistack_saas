<?php

namespace App\Models\Articles;

use App\Models\Core\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ouvrage extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OuvrageItem::class);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'production_cost' => 'decimal:2',
        ];
    }
}
