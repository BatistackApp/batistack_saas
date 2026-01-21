<?php

namespace App\Services\Commerce;

use Illuminate\Support\Facades\DB;

class NumberGeneratorService
{
    public function generateDevisNumber(int $tenantId): string
    {
        return $this->generateNumber('devis', $tenantId, 'DV');
    }

    public function generateCommandeNumber(int $tenantId): string
    {
        return $this->generateNumber('commandes', $tenantId, 'CMD');
    }

    public function generateFactureNumber(int $tenantId): string
    {
        return $this->generateNumber('factures', $tenantId, 'FAC');
    }

    public function generateSituationNumber(int $tenantId): string
    {
        return $this->generateNumber('situations', $tenantId, 'SIT');
    }

    public function generateAvenantNumber(int $tenantId): string
    {
        return $this->generateNumber('avenants', $tenantId, 'AV');
    }

    public function generateAvoirNumber(int $tenantId): string
    {
        return $this->generateNumber('avoirs', $tenantId, 'AVR');
    }

    private function generateNumber(string $table, int $tenantId, string $prefix): string
    {
        return DB::transaction(function () use ($table, $tenantId, $prefix) {
            $sequence = DB::table($table)
                ->where('tenant_id', $tenantId)
                ->lockForUpdate()
                ->count() + 1;

            $year = now()->year;

            return sprintf('%s-%s-%06d', $prefix, $year, $sequence);
        });
    }
}
