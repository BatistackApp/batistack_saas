<?php

namespace App\Models\GED;

use App\Models\User;
use App\Observers\GED\DocumentObserver;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([DocumentObserver::class])]
class Document extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $guarded = [];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(DocumentFolder::class, 'folder_id');
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'expires_at' => 'date',
            'size' => 'integer',
            'version' => 'integer',
        ];
    }
}
