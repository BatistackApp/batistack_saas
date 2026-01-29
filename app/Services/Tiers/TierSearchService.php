<?php

namespace App\Services\Tiers;

use App\Models\Tiers\Tiers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;

class TierSearchService
{
    private Builder $query;
    public function __construct()
    {
        $this->query = Tiers::query();
    }

    public function search(string $keyword): self
    {
        $this->query->where(function (Builder $q) use ($keyword) {
            $q->where('code_tiers', 'like', "%{$keyword}%")
                ->orWhere('raison_social', 'like', "%{$keyword}%")
                ->orWhere('nom', 'like', "%{$keyword}%")
                ->orWhere('prenom', 'like', "%{$keyword}%")
                ->orWhere('email', 'like', "%{$keyword}%")
                ->orWhere('telephone', 'like', "%{$keyword}%")
                ->orWhere('siret', 'like', "%{$keyword}%");
        });

        return $this;
    }

    public function byType(string $type): self
    {
        $this->query->whereHas('types', function (Builder $q) use ($type) {
            $q->where('type', $type);
        });

        return $this;
    }

    public function byStatus(string $status): self
    {
        $this->query->where('status', $status);

        return $this;
    }

    public function byEntity(string $entity): self
    {
        $this->query->where('type_entite', $entity);

        return $this;
    }

    public function active(): self
    {
        return $this->byStatus('active');
    }

    public function withTypes(): self
    {
        $this->query->with('types');

        return $this;
    }

    public function paginate(int $perPage = 15): Paginator
    {
        return $this->query->paginate($perPage);
    }

    public function get(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->query->get();
    }

    public function first(): ?Tiers
    {
        return $this->query->first();
    }
}
