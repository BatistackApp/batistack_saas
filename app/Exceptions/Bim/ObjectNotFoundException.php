<?php

namespace App\Exceptions\Bim;

use Illuminate\Http\Response;

class ObjectNotFoundException extends BimModuleException
{
    protected $code = Response::HTTP_NOT_FOUND;

    protected $message = "L'élément 3D spécifié est introuvable dans ce modèle.";
}
