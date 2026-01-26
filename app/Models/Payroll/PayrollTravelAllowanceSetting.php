<?php

namespace App\Models\Payroll;

use App\Enums\Payroll\TravelAllowanceType;
use App\Models\Core\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollTravelAllowanceSetting extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    protected function casts(): array
    {
        return [
            'uuid' => 'string',
            'type' => TravelAllowanceType::class,
            'rate_per_km' => 'decimal:3',
            'forfeit_amount' => 'decimal:2',
            'max_amount_per_day' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
