<?php

namespace App\Models\Articles;

use App\Models\Core\Tenants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleCategory extends Model
{
    use HasFactory;

    public function tenants(): BelongsTo
    {
        return $this->belongsTo(Tenants::class);
    }
}
