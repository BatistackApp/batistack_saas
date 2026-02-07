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
    protected $signature = 'locations:check-expiring';

    protected $description = 'Alerte les responsables 48h avant la fin d\'une location';

    public function handle(): void
    {
        $contracts = RentalContract::where('status', RentalStatus::ACTIVE)
            ->whereDate('end_date_planned', now()->addDays(2))
            ->get();

        foreach ($contracts as $contract) {
            // On notifie les utilisateurs ayant la permission de gérer les locations
            $managers = User::permission('locations.manage')->get();
            Notification::send($managers, new RentalContractEndingSoonNotification($contract));
        }
    }
}
