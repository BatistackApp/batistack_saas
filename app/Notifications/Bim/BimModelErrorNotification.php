<?php

namespace App\Notifications\Bim;

use App\Models\Bim\BimModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class BimModelErrorNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public BimModel $model, public string $error) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'model_id' => $this->model->id,
            'error' => $this->error,
            'message' => "Erreur lors du traitement de la maquette {$this->model->name}.",
        ];
    }
}
