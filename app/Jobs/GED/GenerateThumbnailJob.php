<?php

namespace App\Jobs\GED;

use App\Models\GED\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
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

        Storage::disk('public')->makeDirectory($destFolder);
        $destPath = Storage::disk('public')->path($destFolder.'/'.$thumbName);

        try {
            if ($this->document->extension === 'pdf') {
                $pdf = new Pdf($sourcePath);
                $pdf->saveImage($destPath);
            } elseif (in_array($this->document->extension, ['jpg', 'jpeg', 'png', 'webp'])) {
                // Utilisation simple de GD ou Intervention Image
                $img = imagecreatefromstring(Storage::disk('public')->get($this->document->file_path));
                $scaled = imagescale($img, 300);
                imagejpeg($scaled, $destPath);
            }

            // Mise à jour des métadonnées du document avec le chemin de la miniature
            $this->document->update([
                'metadata' => array_merge($this->document->metadata ?? [], ['thumbnail' => $destFolder.'/'.$thumbName]),
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur miniature GED: '.$e->getMessage());
        }
    }
}
