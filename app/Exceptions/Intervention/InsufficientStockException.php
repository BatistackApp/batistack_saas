<?php

namespace App\Exceptions\Intervention;

/**
 * Lancée si les stocks sont insuffisants pour valider l'intervention.
 */
class InsufficientStockException extends InterventionModuleException {}
