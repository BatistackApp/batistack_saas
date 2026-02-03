<?php

namespace App\Jobs\Commerce;

use App\Models\Commerce\Invoices;
use App\Models\User;
use App\Notifications\Commerce\InvoiceOverdueNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use Log;

class ProcessOverdueInvoicesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $overdueInvoices = Invoices::where('status', 'validated')
            ->where('due_date', '<', now())
            ->with(['customer', 'project'])
            ->get();

        foreach ($overdueInvoices as $invoice) {
            // 1. Marquer comme "En retard" si ce n'est pas déjà fait
            $invoice->update(['status' => 'overdue']);

            // 2. Alerter les gestionnaires financiers (Comptabilité / Direction)
            $recipients = User::role(['accountant', 'tenant_admin'])->get();

            if ($recipients->isNotEmpty()) {
                Notification::send($recipients, new InvoiceOverdueNotification($invoice));
            }

            Log::warning("Alerte retard de paiement : Facture {$invoice->reference} (Client: {$invoice->customer->name})");
        }
    }
}
