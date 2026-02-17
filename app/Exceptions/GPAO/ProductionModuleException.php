<?php

namespace App\Exceptions\GPAO;

use Exception;
use Illuminate\Http\Response;

class ProductionModuleException extends Exception
{
    protected $code = Response::HTTP_UNPROCESSABLE_ENTITY;

    public function __construct(string $message = '', int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code ?: $this->code, $previous);
    }
}
