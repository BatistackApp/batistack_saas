<?php

namespace App\Models\Bim;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BimView extends Model
{
    use HasFactory;

    protected $fillable = [
        'bim_model_id',
        'user_id',
        'name',
        'camera_state',
    ];

    public function model(): BelongsTo
    {
        return $this->belongsTo(BimModel::class, 'bim_model_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'camera_state' => 'array', // Position {x,y,z}, rotation, target, etc.
        ];
    }
}
