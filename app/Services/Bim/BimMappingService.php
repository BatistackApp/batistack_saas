<?php

namespace App\Services\Bim;

use App\Models\Bim\BimMapping;
use App\Models\Bim\BimObject;
use Illuminate\Database\Eloquent\Model;

class BimMappingService
{
    /**
     * Lie un objet 3D à une ressource Batistack (Article, Phase, etc.).
     */
    public function mapObjectToResource(BimObject $object, Model $resource, array $options = []): BimMapping
    {
        return BimMapping::updateOrCreate(
            [
                'bim_object_id' => $object->id,
                'mappable_id' => $resource->getKey(),
                'mappable_type' => $resource->getMorphClass(),
            ],
            [
                'color_override' => $options['color'] ?? null,
                'metadata' => $options['metadata'] ?? [],
            ]
        );
    }

    /**
     * Récupère toutes les données métier associées à un GUID IFC.
     */
    public function getBusinessContext(string $guid, int $modelId): array
    {
        $object = BimObject::where('guid', $guid)
            ->where('bim_model_id', $modelId)
            ->with(['mappings.mappable'])
            ->first();

        if (! $object) {
            return ['found' => false];
        }

        return [
            'found' => true,
            'object' => $object,
            'properties' => $object->properties,
            'linked_resources' => $object->mappings->map(fn ($m) => [
                'type' => $m->mappable_type,
                'data' => $m->mappable,
                'color' => $m->color_override,
            ]),
        ];
    }
}
