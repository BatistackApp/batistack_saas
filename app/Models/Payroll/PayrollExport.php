<?php

namespace App\Models\Payroll;

use App\Enums\Payroll\PayrollExportFormat;
use App\Models\Core\Tenant;
use App\Observers\Payroll\PayrollExportObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([PayrollExportObserver::class])]
class PayrollExport extends Model
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
            'year' => 'integer',
            'exported_at' => 'timestamp',
            'format' => PayrollExportFormat::class,
        ];
    }
}
