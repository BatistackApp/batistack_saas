<?php

namespace App\Exceptions\GPAO;

use Exception;

/**
 * Lancée si les matières premières sont insuffisantes pour lancer l'OF.
 */
class InsufficientMaterialException extends ProductionModuleException
{
}
