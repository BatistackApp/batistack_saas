<?php

namespace App\Notifications\Bim;

use App\Models\Bim\BimModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BimModelReadyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public BimModel $model) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Maquette 3D prête : {$this->model->name}")
            ->line("Le traitement de la maquette numérique pour le projet {$this->model->project->name} est terminé.")
            ->line('Vous pouvez désormais visualiser le projet en 3D et lier les objets aux données métier.');
        // ->action('Ouvrir le Viewer 3D', url("/admin/bim/viewer/{$this->model->id}"));
    }

    public function toDatabase($notifiable): array
    {
        return [];
    }

    public function toArray($notifiable): array
    {
        return [
            'model_id' => $this->model->id,
            'name' => $this->model->name,
            'message' => "La maquette 3D '{$this->model->name}' est prête pour l'exploitation.",
        ];
    }
}
