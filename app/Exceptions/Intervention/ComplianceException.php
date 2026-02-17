<?php

namespace App\Exceptions\Intervention;

use Illuminate\Http\Response;

/**
 * Lancée si le client ou le projet n'est pas conforme (ex: suspendu).
 */
class ComplianceException extends InterventionModuleException
{
    protected $code = Response::HTTP_FORBIDDEN;
}
