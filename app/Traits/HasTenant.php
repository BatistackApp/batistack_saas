<?php

namespace App\Traits;
use App\Models\Core\Tenants;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * Trait BelongsToTenant
 * * Ce trait permet d'automatiser l'isolation des données par Tenant.
 * Il doit être utilisé sur tous les modèles qui possèdent une colonne 'tenants_id'.
 */
trait HasTenant
{
    /**
     * Le "boot" du trait est appelé automatiquement par Eloquent.
     * Il enregistre le scope global et le hook de création.
     */
    protected static function bootHasTenant(): void
    {
        // 1. Applique le scope global pour filtrer les SELECT/UPDATE/DELETE
        static::addGlobalScope(new TenantScope);

        // 2. Injecte le tenants_id automatiquement lors d'un INSERT
        static::creating(function ($model) {
            if (Auth::check() && empty($model->tenants_id)) {
                $model->tenants_id = Auth::user()->tenants_id;
            }
        });
    }

    /**
     * Relation standardisée vers le Tenant propriétaire de la ressource.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenants::class, 'tenants_id');
    }
}

class TenantScope implements Scope
{
    /**
     * Applique le scope au constructeur de requêtes Eloquent.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // On n'applique le filtre que si un utilisateur est connecté
        // Cela évite de bloquer les commandes CLI ou les jobs sans contexte
        if (Auth::check() && Auth::user()->tenants_id) {
            $builder->where($model->getTable() . '.tenants_id', Auth::user()->tenants_id);
        }
    }
}
