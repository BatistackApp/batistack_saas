<?php

namespace App\Observers\Tiers;

use App\Jobs\Tiers\SyncTierContactsJob;
use App\Jobs\Tiers\ValidateTierSiretJob;
use App\Models\Core\AuditLog;
use App\Models\Tiers\Tiers;
use App\Notifications\Tiers\TierCreatedNotification;
use App\Notifications\Tiers\TierUpdatedNotification;

class TierObserver
{
    public function created(Tiers $tiers): void
    {
        $this->logActivity($tiers, 'created');
        $tiers->tenant->users()
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['admin', 'manager']))
            ->each(fn ($user) => $user->notify(new TierCreatedNotification($tiers)));

        ValidateTierSiretJob::dispatch($tiers);
        SyncTierContactsJob::dispatch($tiers);
    }

    public function updated(Tiers $tiers): void
    {
        if ($tiers->isDirty('siret')) {
            ValidateTierSiretJob::dispatch($tier);
        }

        if ($tiers->isDirty()) {
            $changes = $tiers->getDirty();
            $this->logActivity($tiers, 'updated', $changes);

            $tiers->tenant->users()
                ->whereHas('roles', fn ($q) => $q->whereIn('name', ['admin', 'manager']))
                ->each(fn ($user) => $user->notify(new TierUpdatedNotification($tiers, $changes)));
        }
    }

    public function deleted(Tiers $tiers): void
    {
        $this->logActivity($tiers, 'deleted');
    }

    public function restored(Tiers $tiers): void
    {
        $this->logActivity($tiers, 'restored');
    }

    private function logActivity(Tiers $tier, string $action, array $changes = []): void
    {
        AuditLog::create([
            'tenant_id' => $tier->tenant_id,
            'auditable_type' => Tiers::class,
            'auditable_id' => $tier->id,
            'action' => $action,
            'changes' => $changes,
            'user_id' => auth()->id(),
        ]);
    }
}
