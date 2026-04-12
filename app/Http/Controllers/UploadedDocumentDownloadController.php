<?php

namespace App\Http\Controllers;

use App\Models\UploadedDocument;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UploadedDocumentDownloadController extends Controller
{
    public function __invoke(UploadedDocument $document): BinaryFileResponse
    {
        abort_unless(Storage::disk($document->disk)->exists($document->path), 404);

        return response()->download(
            Storage::disk($document->disk)->path($document->path),
            $document->original_name,
            ['Content-Type' => $document->mime_type ?: 'application/octet-stream'],
        );
    }
}
