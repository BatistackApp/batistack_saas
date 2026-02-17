<?php

namespace App\Exceptions\Locations;

use Illuminate\Http\Response;

class ContractLockedException extends RentalModuleException
{
    protected $code = Response::HTTP_FORBIDDEN;

    protected $message = 'Ce contrat de location est verrouillé et ne peut plus être modifié.';
}
