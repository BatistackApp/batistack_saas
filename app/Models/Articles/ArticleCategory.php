<?php

namespace App\Models\Articles;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArticleCategory extends Model
{
    use HasFactory, HasTenant;

    protected $guarded = [];

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }
}
