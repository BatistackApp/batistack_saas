<?php

namespace App\Models\Locations;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalInspection extends Model
{
    use HasFactory;
    protected $fillable = [
        'rental_contract_id', 'inspector_id', 'type', 'notes', 'photos'
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(RentalContract::class, 'rental_contract_id');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    protected function casts(): array
    {
        return [
            'photos' => 'array',
        ];
    }
}
