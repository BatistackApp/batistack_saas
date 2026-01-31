<?php

namespace App\Models\Articles;

use App\Models\Core\Tenants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Warehouse extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function tenants(): BelongsTo
    {
        return $this->belongsTo(Tenants::class);
    }

    public function articles(): BelongsToMany {
        return $this->belongsToMany(Article::class, 'article_warehouse')
            ->withPivot('quantity', 'bin_location');
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }
}
