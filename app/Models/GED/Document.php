<?php

namespace App\Models\GED;

use App\Enums\GED\DocumentStatus;
use App\Enums\GED\DocumentType;
use App\Models\User;
use App\Observers\GED\DocumentObserver;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
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
        return $this->belongsTo(User::class, 'user_id');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
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
            'type' => DocumentType::class,
            'status' => DocumentStatus::class,
            'metadata' => 'array',
            'expires_at' => 'date',
            'validated_at' => 'datetime',
            'is_valid' => 'boolean',
            'size' => 'integer',
        ];
    }

    // --- SCOPES (Utiles pour les tableaux de bord BTP) ---

    public function scopeExpired(Builder $query): void
    {
        $query->where('expires_at', '<', now())
            ->where('status', '!=', DocumentStatus::Archived);
    }

    public function scopePending(Builder $query): void
    {
        $query->where('status', DocumentStatus::PendingValidation);
    }
}
