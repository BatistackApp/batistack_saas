<?php

namespace App\Exceptions\Expense;

use Illuminate\Http\Response;

class ReportLockedException extends ExpenseModuleException
{
    protected $code = Response::HTTP_FORBIDDEN;

    protected $message = 'Cette note de frais est verrouillée car elle a déjà été soumise ou validée.';
}
