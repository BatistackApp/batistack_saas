<?php

namespace App\Observers\Accounting;

use App\Jobs\Accounting\GeneratePeriodClosureReportJob;
use App\Models\Accounting\PeriodClosure;
use App\Notifications\Accounting\PeriodClosedNotification;
use Illuminate\Support\Facades\Notification;

class PeriodClosureObserver
{
    public function created(PeriodClosure $closure): void
    {
        // Verrouiller toutes les écritures de cette période
        $closure->period_start;
        $closure->period_end;

        // Notifier l'équipe comptable
        $users = auth()->user(); // À adapter selon la structure RH

        Notification::send($users, new PeriodClosedNotification($closure));

        // Dispatcher un job pour générer le rapport de clôture
        GeneratePeriodClosureReportJob::dispatch($closure);
    }
}
