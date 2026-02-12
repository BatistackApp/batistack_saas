<?php

namespace App\Enums\Bim;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum BimModelStatus: string implements HasLabel, HasColor
{
    case UPLOADING = 'uploading';   // Transfert vers S3
    case PROCESSING = 'processing'; // Extraction des métadonnées
    case READY = 'ready';           // Disponible pour visualisation
    case ERROR = 'error';           // Échec du parsing IFC

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::UPLOADING => 'blue',
            self::PROCESSING => 'yellow',
            self::READY => 'green',
            self::ERROR => 'red',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::UPLOADING => __('bim.model.status.uploading'),
            self::PROCESSING => __('bim.model.status.processing'),
            self::READY => __('bim.model.status.ready'),
            self::ERROR => __('bim.model.status.error'),
        };
    }
}
