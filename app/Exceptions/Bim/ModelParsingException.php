<?php

namespace App\Exceptions\Bim;

class ModelParsingException extends BimModuleException
{
    protected $message = 'Échec du traitement de la maquette : le format IFC est invalide ou corrompu.';
}
