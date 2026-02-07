<?php

namespace App\Exceptions\GPAO;

use Exception;

/**
 * Lancée si l'ordre des opérations (séquence) n'est pas respecté.
 */
class InvalidOperationSequenceException extends ProductionModuleException
{
}
