<?php

namespace App\Services\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Spatie\Browsershot\Browsershot;

class DocumentManagementService
{
    public function generatePdf(Model $model, string $view, string $type, array $extraData = []): string
    {
        $tenantId = $model->tenants_id;
        $reference = $model->reference ?? uniqid();

        $fileName = "{$reference}.pdf";
        $subDirectory = "tenants/{$tenantId}/{$type}";
        $fullPath = storage_path("app/public/{$subDirectory}/{$fileName}");

        // 1. Création du répertoire si inexistant
        if (! Storage::disk('public')->exists($subDirectory)) {
            Storage::disk('public')->makeDirectory($subDirectory);
        }

        // 2. Préparation des données de la vue
        $viewData = array_merge([
            $this->getModelName($model) => $model->load($this->getRequiredRelations($model)),
            'tenant' => $model->tenant,
        ], $extraData);

        $html = View::make($view, $viewData)->render();

        // 3. Génération via Browsershot (Config Windows/Linux gérée via config service)
        Browsershot::html($html)
            ->setNodeBinary(config('services.browsershot.node_path'))
            ->setNpmBinary(config('services.browsershot.npm_path'))
            ->format('A4')
            ->margins(10, 10, 10, 10)
            ->showBackground()
            ->save($fullPath);

        // 4. Mise à jour du modèle pour la traçabilité
        $relativePath = "{$subDirectory}/{$fileName}";
        $model->update(['pdf_path' => $relativePath]);

        return $relativePath;
    }

    /**
     * Identifie le nom de variable à passer à la vue selon la classe du modèle.
     */
    protected function getModelName(Model $model): string
    {
        $className = class_basename($model);
        return strtolower($className) === 'invoices' ? 'invoice' : strtolower($className);
    }

    /**
     * Détermine les relations nécessaires pour le rendu PDF.
     */
    protected function getRequiredRelations(Model $model): array
    {
        return match (get_class($model)) {
            \App\Models\Commerce\Quote::class => ['customer', 'items', 'project'],
            \App\Models\Commerce\Invoices::class => ['tiers', 'items.quoteItem', 'project', 'quote'],
            default => [],
        };
    }
}
