<?php

namespace App\Models\Payroll;

use App\Models\Core\Tenants;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollScale extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'tenants_id',
        'name',
        'slug',
        'category',
        'value',
        'type',
        'effective_date',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'metadata' => 'array',
            'value' => 'decimal:4',
        ];
    }

    /**
     * Récupère le barème applicable à une date
     */
    public function scopeForDate($query, $date)
    {
        return $query->where('effective_from', '<=', $date)
            ->orderBy('effective_from', 'desc');
    }
}
