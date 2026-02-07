<?php

namespace App\Exceptions\GPAO;

use Exception;
use Illuminate\Http\Response;

/**
 * Lancée si on tente de modifier un OF déjà terminé ou annulé.
 */
class WorkOrderLockedException extends Exception
{
    protected $code = Response::HTTP_FORBIDDEN;
}
