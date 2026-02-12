<?php

namespace App\Services\Bim;

use App\Models\Bim\BimObject;

class BimPropertyService
{
    /**
     * Extrait les quantités (Surfaces, Volumes) d'un objet pour le chiffrage.
     */
    public function getQuantities(BimObject $object): array
    {
        $props = $object->properties;

        // Recherche dans les jeux de propriétés IFC standard (Pset_WallCommon, etc.)
        return [
            'volume' => $props['Dimensions']['Volume'] ?? 0,
            'area' => $props['Dimensions']['Area'] ?? 0,
            'height' => $props['Dimensions']['Height'] ?? 0,
        ];
    }
}
