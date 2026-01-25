<?php

namespace App\Jobs\Payroll;

use App\Models\Payroll\PayrollSlip;
use App\Services\Payroll\PayrollAccountingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateAccountingEntriesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public PayrollSlip $slip)
    {
    }

    public function handle(PayrollAccountingService $service): void
    {
        try {
            $entry = $service->createAccountingEntries($this->slip);

            Log::info("Accounting entries created for payroll", [
                'payroll_slip_id' => $this->slip->id,
                'journal_entry_id' => $entry->id,
                'amount' => $this->slip->gross_amount,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to create accounting entries", [
                'payroll_slip_id' => $this->slip->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
