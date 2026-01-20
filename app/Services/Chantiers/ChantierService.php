<?php

namespace App\Services\Chantiers;

use App\Enums\Chantiers\ChantierStatus;
use App\Models\Chantiers\Chantier;
use App\Models\Chantiers\ChantierCost;
use Illuminate\Support\Str;

class ChantierService
{
    public function createChantier(array $data): Chantier
    {
        $data['uuid'] = (string) Str::uuid();
        $data['code'] = $data['code'] ?? $this->generateChantierCode();

        return Chantier::create($data);
    }

    public function addCost(Chantier $chantier, array $costData): ChantierCost
    {
        $cost = $chantier->costs()->create($costData);

        $this->recalculateTotalCosts($chantier);

        return $cost;
    }

    public function recalculateTotalCosts(Chantier $chantier): void
    {
        $total = $chantier->costs()->sum('amount');
        $chantier->update(['budget_total' => $total]);
    }

    public function generateChantierCode(): string
    {
        $lastChantier = Chantier::query()
            ->orderByDesc('id')
            ->first();

        $nextNumber = $lastChantier ? ((int) substr($lastChantier->code, 3)) + 1 : 1;

        return 'CHT'.str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public function closeChantier(Chantier $chantier): bool
    {
        return $chantier->update([
            'status' => ChantierStatus::Completed,
            'end_date' => now(),
        ]);
    }
}
