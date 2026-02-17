<?php

namespace App\Jobs\Articles;

use App\Mail\Articles\DormantStockReportMailable;
use App\Models\Articles\Article;
use App\Models\Core\Tenants;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class IdentifyDormantStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $sixMonthsAgo = Carbon::now()->subMonths(6);

        // On traite chaque tenant séparément pour l'isolation des rapports
        Tenants::all()->each(function (Tenants $tenant) use ($sixMonthsAgo) {

            // 1. Récupération des articles dormants pour ce tenant
            $dormantArticles = Article::whereHas('warehouses', function ($query) {
                $query->where('quantity', '>', 0); // Seulement les articles qui ont du stock
            })
                ->whereDoesntHave('movements', function ($query) use ($sixMonthsAgo) {
                    $query->where('created_at', '>', $sixMonthsAgo);
                })
                ->get();

            if ($dormantArticles->isEmpty()) {
                return;
            }

            // 2. Identification des destinataires (Rôle : logistics_manager)
            $recipients = User::where('tenants_id', $tenant->id)
                ->role('logistics_manager')
                ->get();

            if ($recipients->isEmpty()) {
                return;
            }

            // 3. Envoi personnalisé à chaque utilisateur (pour avoir le "Bonjour M. [Nom]")
            foreach ($recipients as $recipient) {
                Mail::to($recipient)->send(new DormantStockReportMailable($dormantArticles, $tenant, $recipient));
            }
        });
    }
}
