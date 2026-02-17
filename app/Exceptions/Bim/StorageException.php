<?php

namespace App\Exceptions\Bim;

class StorageException extends BimModuleException
{
    protected $message = "Impossible d'accéder au fichier de la maquette sur le stockage distant.";
}
