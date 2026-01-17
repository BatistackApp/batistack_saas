<?php

namespace App\Services\Tiers;

use App\Enums\Tiers\TierType;
use App\Models\Tiers\Tiers;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TierService
{
    public function createTier(int $tenantId, array $data): Tiers
    {
        return Tiers::create([
            'tenant_id' => $tenantId,
            'name' => $data['name'],
            'slug' => $this->generateSlug($data['name']),
            'description' => $data['description'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'website' => $data['website'] ?? null,
            'siret' => $data['siret'] ?? null,
            'vat_number' => $data['vat_number'] ?? null,
            'iban' => $data['iban'] ?? null,
            'bic' => $data['bic'] ?? null,
            'types' => $data['types'] ?? [],
            'discount_percentage' => $data['discount_percentage'] ?? 0,
            'payment_delay_days' => $data['payment_delay_days'] ?? 0,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    public function updateTier(Tiers $tiers, array $data): Tiers
    {
        $tiers->update([
            'name' => $data['name'] ?? $tiers->name,
            'description' => $data['description'] ?? $tiers->description,
            'email' => $data['email'] ?? $tiers->email,
            'phone' => $data['phone'] ?? $tiers->phone,
            'website' => $data['website'] ?? $tiers->website,
            'siret' => $data['siret'] ?? $tiers->siret,
            'vat_number' => $data['vat_number'] ?? $tiers->vat_number,
            'iban' => $data['iban'] ?? $tiers->iban,
            'bic' => $data['bic'] ?? $tiers->bic,
            'types' => $data['types'] ?? $tiers->types,
            'discount_percentage' => $data['discount_percentage'] ?? $tiers->discount_percentage,
            'payment_delay_days' => $data['payment_delay_days'] ?? $tiers->payment_delay_days,
            'is_active' => $data['is_active'] ?? $tiers->is_active,
        ]);

        return $tiers;
    }

    public function deleteTier(Tiers $tiers): bool
    {
        return $tiers->delete();
    }

    public function getTierWithRelations(int $tierId, int $tenantId): ?Tiers
    {
        return Tiers::query()
            ->where('tenant_id', $tenantId)
            ->where('id', $tierId)
            ->with([
                'addresses' => fn ($q) => $q->orderBy('is_default', 'desc'),
                'contacts' => fn ($q) => $q->orderBy('is_primary', 'desc'),
            ])
            ->first();
    }

    public function searchTiers(int $tenantId, array $filters): LengthAwarePaginator
    {
        $query = Tiers::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true);

        if (! empty($filters['type'])) {
            $query->whereJsonContains('types', $filters['type']);
        }

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('siret', 'like', "%{$filters['search']}%")
                    ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        if ($filters['has_siret'] ?? false) {
            $query->whereNotNull('siret');
        }

        return $query->orderBy('name')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function getTiersByType(int $tenantId, TierType $type): Collection
    {
        return Tiers::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->whereJsonContains('types', $type->value)
            ->orderBy('name')
            ->get();
    }

    private function generateSlug(string $name): string
    {
        $baseSlug = \Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (Tiers::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
