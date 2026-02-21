<?php

namespace App\Console\Commands\Locations;

use App\Enums\Locations\RentalStatus;
use App\Models\Locations\RentalContract;
use App\Models\User;
use App\Notifications\Locations\RentalContractEndingSoonNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class CheckExpiringRentalsCommand extends Command
{
    protected $signature = 'locations:check-alerts';

    protected $description = 'Vérifie les fins de location et les retours matériels en attente.';

    public function handle(): void
    {
        $this->checkUpcomingEnds();
        $this->checkStaleOffHire();

        $this->info('Alertes de location vérifiées avec succès.');
    }

    /**
     * Alerte 48h avant la fin prévue.
     */
    protected function checkUpcomingEnds(): void
    {
        $contracts = RentalContract::where('status', RentalStatus::ACTIVE)
            ->whereDate('end_date_planned', now()->addDays(2))
            ->get();

        foreach ($contracts as $contract) {
            // Notification aux gestionnaires du parc et au conducteur de travaux
            $recipients = User::permission('locations.manage')->get();
            Notification::send($recipients, new RentalContractEndingSoonNotification($contract, 'ending_soon'));
        }
    }

    /**
     * Alerte si le matériel est en Off-Hire depuis plus de 3 jours sans être rendu.
     * (Évite que le loueur ne "traîne" pour récupérer le matériel sur un chantier exigu).
     */
    protected function checkStaleOffHire(): void
    {
        $contracts = RentalContract::where('status', RentalStatus::OFF_HIRE)
            ->where('off_hire_requested_at', '<=', now()->subDays(3))
            ->whereNull('actual_return_at')
            ->get();

        foreach ($contracts as $contract) {
            $recipients = User::permission('locations.manage')->get();
            Notification::send($recipients, new RentalContractEndingSoonNotification($contract, 'stale_off_hire'));
        }
    }
}
