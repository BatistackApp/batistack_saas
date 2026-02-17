<?php

namespace App\Models\Banque;

use App\Enums\Banque\BankPaymentMethod;
use App\Models\Commerce\Invoices;
use App\Models\User;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory, HasTenant;

    protected $guarded = [];

    public function bankTransaction(): BelongsTo
    {
        return $this->belongsTo(BankTransaction::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoices::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected function casts(): array
    {
        return [
            'method' => BankPaymentMethod::class,
            'payment_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }
}
