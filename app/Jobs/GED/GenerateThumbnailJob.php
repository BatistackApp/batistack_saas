<?php

namespace App\Jobs\GED;

use App\Models\GED\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Spatie\ImageOptimizer\OptimizerChainFactory;
use Spatie\PdfToImage\Pdf;

class GenerateThumbnailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Document $document) {}

    public function handle(): void
    {
        $sourcePath = Storage::disk('public')->path($this->document->file_path);
        $thumbName = 'thumb_'.pathinfo($this->document->file_name, PATHINFO_FILENAME).'.jpg';
        $destFolder = "tenants/{$this->document->tenants_id}/thumbnails";

        if (! Storage::disk('public')->exists($destFolder)) {
            Storage::disk('public')->makeDirectory($destFolder);
        }
        $destRelativePath = $destFolder.'/'.$thumbName;
        $destFullPath = Storage::disk('public')->path($destRelativePath);

        try {
            // 1. Génération de la miniature
            if ($this->document->extension === 'pdf') {
                $pdf = new Pdf($sourcePath);
                $pdf->saveImage($destFullPath);
            } elseif (in_array(strtolower($this->document->extension), ['jpg', 'jpeg', 'png', 'webp'])) {
                $img = imagecreatefromstring(Storage::disk('public')->get($this->document->file_path));
                $scaled = imagescale($img, 300);
                imagejpeg($scaled, $destFullPath, 80);
                imagedestroy($img);
                imagedestroy($scaled);
            }

            // 2. Optimisation de la miniature (si générée)
            if (file_exists($destFullPath)) {
                OptimizerChainFactory::create()->optimize($destFullPath);
            }

            // 3. Mise à jour des métadonnées
            $this->document->update([
                'metadata' => array_merge($this->document->metadata ?? [], [
                    'thumbnail' => $destRelativePath,
                    'has_thumbnail' => true,
                ]),
            ]);

        } catch (\Exception $e) {
            \Log::error("Erreur traitement média GED [Doc ID: {$this->document->id}]: ".$e->getMessage());
        }
    }
}
