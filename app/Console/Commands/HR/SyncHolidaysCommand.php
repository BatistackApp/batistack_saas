<?php

namespace App\Console\Commands\HR;

use App\Models\Core\TenantInfoHolidays;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncHolidaysCommand extends Command
{
    protected $signature = 'hr:sync-holidays {tenant_id} {year?}';

    protected $description = 'Pré-remplit les jours fériés français standards pour un tenant';

    public function handle(): void
    {
        $tenantId = $this->argument('tenant_id');
        $year = $this->argument('year') ?? date('Y');

        $holidays = $this->getFrenchHolidays($year);

        foreach ($holidays as $date => $label) {
            TenantInfoHolidays::updateOrCreate(
                ['tenants_id' => $tenantId, 'date' => $date],
                ['label' => $label]
            );
        }

        $this->info("Jours fériés synchronisés pour le tenant $tenantId pour l'année $year.");
    }

    private function getFrenchHolidays(int $year): array
    {
        $easter = Carbon::createFromTimestamp(easter_date($year));

        return [
            "$year-01-01" => "Jour de l'an",
            $easter->copy()->addDay()->format('Y-m-d') => 'Lundi de Pâques',
            "$year-05-01" => 'Fête du Travail',
            "$year-05-08" => 'Victoire 1945',
            $easter->copy()->addDays(39)->format('Y-m-d') => 'Ascension',
            $easter->copy()->addDays(50)->format('Y-m-d') => 'Lundi de Pentecôte',
            "$year-07-14" => 'Fête Nationale',
            "$year-08-15" => 'Assomption',
            "$year-11-01" => 'Toussaint',
            "$year-11-11" => 'Armistice',
            "$year-12-25" => 'Noël',
        ];
    }
}
