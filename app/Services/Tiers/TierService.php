<?php

namespace App\Services\Tiers;

use App\Enums\Tiers\TierType;
use App\Models\Tiers\Tiers;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class TierService
{
    private const MAX_SLUG_RETRIES = 5;

    private const INITIAL_BACKOFF_MS = 100;

    public function createTier(int $tenantId, array $data): Tiers
    {
        $attempt = 0;

        while ($attempt < self::MAX_SLUG_RETRIES) {
            try {
                $slug = $this->generateSlug($data['name'], $attempt);

                return Tiers::create([
                    'tenant_id' => $tenantId,
                    'name' => $data['name'],
                    'slug' => $slug,
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
            } catch (QueryException $e) {
                if (! $this->isUniqueConstraintViolation($e)) {
                    throw $e;
                }

                $attempt++;

                if ($attempt >= self::MAX_SLUG_RETRIES) {
                    Log::error('Failed to create tier after max retries', [
                        'tenant_id' => $tenantId,
                        'tier_name' => $data['name'],
                        'attempts' => $attempt,
                    ]);

                    throw $e;
                }

                $backoffMs = self::INITIAL_BACKOFF_MS * (2 ** ($attempt - 1));
                usleep($backoffMs * 1000);
            }
        }
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

    private function generateSlug(string $name, int $attempt = 0): string
    {
        $baseSlug = \Str::slug($name);
        $suffix = $attempt === 0 ? '' : $this->generateSuffix($attempt);
        $slug = $suffix ? "{$baseSlug}-{$suffix}" : $baseSlug;

        return $slug;
    }

    private function generateSuffix(int $attempt): string
    {
        if ($attempt === 1) {
            return \Str::random(4);
        }

        return \Str::random(6);
    }

    private function isUniqueConstraintViolation(QueryException $e): bool
    {
        return \Str::contains($e->getMessage(), [
            'UNIQUE constraint failed',
            'Duplicate entry',
            'duplicate key',
        ]);
    }
}
