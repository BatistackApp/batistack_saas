<?php

namespace App\Exceptions\Intervention;

use Exception;

/**
 * Lancée lors d'un passage de statut non autorisé.
 */
class InvalidStatusTransitionException extends InterventionModuleException
{
}
