<?php

namespace App\Observers\Bim;

use App\Enums\Bim\BimModelStatus;
use App\Jobs\Bim\ProcessBimModelJob;
use App\Models\Bim\BimModel;

class BimModelObserver
{
    public function created(BimModel $model): void
    {
        if ($model->status === BimModelStatus::UPLOADING) {
            ProcessBimModelJob::dispatch($model);
        }
    }
}
