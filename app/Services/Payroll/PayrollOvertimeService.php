<?php

namespace App\Services\Payroll;

use App\Models\Payroll\PayrollOvertime;
use App\Models\Payroll\PayrollSlip;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PayrollOvertimeService
{
    /**
     * Calcule les heures supplémentaires pour une fiche de paie
     */
    public function calculateForPayrollSlip(
        PayrollSlip $slip,
        Collection $timesheets,
    ): array {
        $overtimes = $this->groupOvertimesByType($timesheets, $slip->period_start, $slip->period_end);

        $totalAmount = 0;
        $lines = [];

        foreach ($overtimes as $type => $data) {
            $amount = $this->calculateOvertimeAmount(
                $data['hours'],
                $data['hourly_rate'],
                $type,
            );

            $totalAmount += $amount;

            $lines[] = [
                'type' => $type,
                'hours' => $data['hours'],
                'hourly_rate' => $data['hourly_rate'],
                'multiplier' => $this->getMultiplierForType($type),
                'amount' => $amount,
            ];
        }

        return [
            'total_amount' => $totalAmount,
            'lines' => $lines,
        ];
    }

    /**
     * Groupe les heures supplémentaires par type
     */
    private function groupOvertimesByType(
        Collection $timesheets,
        Carbon $periodStart,
        Carbon $periodEnd,
    ): array {
        $overtimes = [
            'standard' => ['hours' => 0, 'hourly_rate' => 0],
            'night' => ['hours' => 0, 'hourly_rate' => 0],
            'sunday' => ['hours' => 0, 'hourly_rate' => 0],
            'public' => ['hours' => 0, 'hourly_rate' => 0],
        ];

        foreach ($timesheets as $timesheet) {
            if ($timesheet->hours <= 0 || $timesheet->type !== 'work') {
                continue;
            }

            $type = $this->determineOvertimeType($timesheet->timesheet_date);

            if (isset($overtimes[$type])) {
                $overtimes[$type]['hours'] += $timesheet->hours;
                $overtimes[$type]['hourly_rate'] = $timesheet->hourly_rate ?? 0;
            }
        }

        return array_filter($overtimes, fn ($data) => $data['hours'] > 0);
    }

    /**
     * Détermine le type d'heure supplémentaire selon la date
     */
    private function determineOvertimeType(Carbon $date): string
    {
        if ($date->isSunday()) {
            return 'sunday';
        }

        // À adapter selon les jours fériés du contexte (FR)
        if ($this->isPublicHoliday($date)) {
            return 'public';
        }

        // À adapter selon les heures de nuit (ex: 22h à 6h)
        if ($this->isNightHours($date)) {
            return 'night';
        }

        return 'standard';
    }

    /**
     * Vérifie si c'est un jour férié (France)
     */
    private function isPublicHoliday(Carbon $date): bool
    {
        $publicHolidays = [
            '01-01', // Jour de l'an
            '05-01', // Fête du Travail
            '05-08', // Victoire 1945
            '07-14', // Bastille
            '08-15', // Assomption
            '11-01', // Toussaint
            '11-11', // Armistice
            '12-25', // Noël
        ];

        return in_array($date->format('m-d'), $publicHolidays);
    }

    /**
     * Vérifie si c'est pendant les heures de nuit
     */
    private function isNightHours(Carbon $date): bool
    {
        $hour = $date->hour;

        return $hour >= 22 || $hour < 6;
    }

    /**
     * Récupère le multiplicateur pour le type d'heure supplémentaire
     */
    private function getMultiplierForType(string $type): int
    {
        return match ($type) {
            'standard' => 125,  // 25% majoration
            'night' => 150,     // 50% majoration
            'sunday' => 150,    // 50% majoration
            'public' => 200,    // 100% majoration
            default => 100,
        };
    }

    /**
     * Calcule le montant de l'heure supplémentaire
     */
    private function calculateOvertimeAmount(
        float $hours,
        float $hourlyRate,
        string $type,
    ): float {
        $multiplier = $this->getMultiplierForType($type);

        return ($hours * $hourlyRate * $multiplier) / 100;
    }

    /**
     * Crée les lignes d'heures supplémentaires pour la fiche de paie
     */
    public function createOvertimeLines(
        PayrollSlip $slip,
        array $calculations,
    ): void {
        foreach ($calculations['lines'] as $line) {
            PayrollOvertime::create([
                'payroll_slip_id' => $slip->id,
                'type' => $line['type'],
                'hours' => $line['hours'],
                'hourly_rate' => $line['hourly_rate'],
                'multiplier' => $line['multiplier'],
                'amount' => $line['amount'],
                'description' => ucfirst($line['type'])." - {$line['hours']}h",
            ]);
        }
    }
}
