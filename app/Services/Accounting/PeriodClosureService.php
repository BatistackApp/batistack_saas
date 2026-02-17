<?php

namespace App\Services\Accounting;

use App\Models\Accounting\PeriodClosure;
use Carbon\CarbonImmutable;
use DB;

class PeriodClosureService
{
    /**
     * Clôture une période comptable.
     * Après clôture, aucune modification n'est possible sur cette période.
     */
    public function closePeriod(int $month, int $year): PeriodClosure
    {
        $periodStart = CarbonImmutable::createFromDate($year, $month, 1)->startOfMonth();
        $periodEnd = CarbonImmutable::createFromDate($year, $month, 1)->endOfMonth();

        return DB::transaction(function () use ($month, $year, $periodStart, $periodEnd) {
            return PeriodClosure::updateOrCreate(
                ['month' => $month, 'year' => $year],
                [
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'is_locked' => true,
                    'closed_by' => auth()->id(),
                    'closed_at' => now(),
                ]
            );
        });
    }

    /**
     * Vérifie si une période est clôturée.
     */
    public function isPeriodClosed(CarbonImmutable $date): bool
    {
        return PeriodClosure::where('month', $date->month)
            ->where('year', $date->year)
            ->where('is_locked', true)
            ->exists();
    }

    /**
     * Empêche les modifications sur une période clôturée.
     */
    public function preventModificationIfClosed(CarbonImmutable $date): void
    {
        if ($this->isPeriodClosed($date)) {
            throw new \RuntimeException(
                "La période {$date->format('m/Y')} est clôturée. Aucune modification possible."
            );
        }
    }

    /**
     * Génère un rapport de clôture (Balance générale de la période).
     */
    public function generateClosureReport(int $month, int $year): array
    {
        $startDate = CarbonImmutable::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = CarbonImmutable::createFromDate($year, $month, 1)->endOfMonth();

        $balanceCalculator = app(BalanceCalculator::class);
        $balances = $balanceCalculator->calculateAllBalances($endDate);

        $totalDebit = array_sum(array_column($balances, 'debit'));
        $totalCredit = array_sum(array_column($balances, 'credit'));

        return [
            'period' => "{$month}/{$year}",
            'start_date' => $startDate,
            'end_date' => $endDate,
            'balances' => $balances,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'is_balanced' => bccomp((string) $totalDebit, (string) $totalCredit, 4) === 0,
        ];
    }
}
