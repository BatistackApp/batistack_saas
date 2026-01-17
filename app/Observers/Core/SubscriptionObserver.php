<?php

namespace App\Observers\Core;

use App\Enums\Core\SubscriptionStatus;
use App\Models\Core\TenantSubscription;
use Illuminate\Support\Facades\Notification;

class SubscriptionObserver
{
    public function created(TenantSubscription $subscription): void
    {
        SyncSubscriptionsJob::dispatch($subscription);
    }

    public function updated(TenantSubscription $subscription): void
    {
        if ($subscription->wasChanged('status')) {
            $status = $subscription->status;
            if ($status === SubscriptionStatus::Active->value) {
                // activer modules/entitlements
                $subscription->tenant->enableModulesForPlan($subscription->plan_id);
            } elseif ($status === SubscriptionStatus::Cancelled->value) {
                // plan de fin : schedule disable, notifications
                $subscription->tenant->schedulePlanDisable($subscription);
            } elseif ($status === SubscriptionStatus::Trialing->value) {
                // notifier fin de trial prochainement
                Notification::send($subscription->tenant->users, new SubscriptionExpiring($subscription));
            }
        }
    }
}
