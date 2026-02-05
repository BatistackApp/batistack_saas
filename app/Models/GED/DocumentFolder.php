<?php

namespace App\Models\GED;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentFolder extends Model
{
    use HasFactory, HasTenant;

    protected $guarded = [];

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
