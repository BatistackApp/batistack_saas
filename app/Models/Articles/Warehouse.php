<?php

namespace App\Models\Articles;

use App\Models\Core\Tenants;
use App\Models\User;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Warehouse extends Model
{
    use HasFactory, HasTenant;
    protected $guarded = [];

    public function responsibleUser(): BelongsTo {
        return $this->belongsTo(User::class, 'responsible_user_id');
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
