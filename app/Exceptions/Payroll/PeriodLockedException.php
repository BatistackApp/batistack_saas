<?php

namespace App\Exceptions\Payroll;

use Exception;
use Illuminate\Http\Response;

class PeriodLockedException extends PayrollModuleException
{
    protected $message = "La période de paie est clôturée et ne peut plus être modifiée.";
    protected $code = Response::HTTP_FORBIDDEN;
}
