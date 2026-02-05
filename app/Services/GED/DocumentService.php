<?php

namespace App\Services\GED;

use App\Models\GED\Document;
use Illuminate\Support\Facades\Storage;

class DocumentService
{
    public function rename(Document $document, string $newName): Document
    {
        $document->update(['name' => $newName]);
        return $document;
    }

    public function checkFileExists(string $path): bool
    {
        return Storage::disk('s3')->exists($path);
    }

    public function getPreviewUrl(Document $document): string
    {
        return Storage::disk('s3')->temporaryUrl(
            $document->file_path,
            now()->addMinutes(15)
        );
    }
}
