<?php

namespace App\Jobs\Articles;

use App\Models\Articles\InventorySession;
use App\Models\User;
use App\Services\Articles\StockMovementService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class ProcessInventoryValidationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private InventorySession $session)
    {
    }

    public function handle(StockMovementService $movementService): void
    {
        $movementService->validateInventorySession($this->session);
        $recipients = User::role(['tenant_admin', 'accountant'])->get();

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new InventorySessionValidatedNotification($this->session));
        }
    }
}
