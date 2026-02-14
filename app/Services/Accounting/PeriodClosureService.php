<?php

namespace App\Services\Accounting;

use App\Models\Accounting\PeriodClosure;
use Carbon\Carbon;
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
        $periodStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $periodEnd = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        return DB::transaction(function () use ($month, $year, $periodStart, $periodEnd) {
            $closure = PeriodClosure::updateOrCreate(
                ['month' => $month, 'year' => $year],
                [
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'is_locked' => true,
                    'closed_by' => auth()->id(),
                    'closed_at' => now(),
                ]
            );

            return $closure;
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
    public function preventModificationIfClosed(Carbon $date): void
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
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

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
