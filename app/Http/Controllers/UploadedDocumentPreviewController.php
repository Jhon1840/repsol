<?php

namespace App\Http\Controllers;

use App\Models\UploadedDocument;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class UploadedDocumentPreviewController extends Controller
{
    public function __invoke(UploadedDocument $document): Response
    {
        abort_unless(Storage::disk($document->disk)->exists($document->path), 404);

        return response()->file(
            Storage::disk($document->disk)->path($document->path),
            [
                'Content-Type' => $document->mime_type ?: 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$document->original_name.'"',
            ]
        );
    }
}
