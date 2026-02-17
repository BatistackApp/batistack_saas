<?php

namespace App\Models\GPAO;

use App\Models\Articles\Article;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id', 'article_id', 'label',
        'quantity_planned', 'quantity_consumed', 'unit_cost_ht',
    ];

    protected function casts(): array
    {
        return [
            'quantity_planned' => 'decimal:3',
            'quantity_consumed' => 'decimal:3',
            'unit_cost_ht' => 'decimal:2',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
