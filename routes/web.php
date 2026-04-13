<?php

use App\Http\Controllers\SessionRefreshController;
use App\Http\Controllers\UploadedDocumentDownloadController;
use App\Http\Controllers\UploadedDocumentPreviewController;
use App\Http\Controllers\RiderLookupController;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/consulta-puntos/');
Route::middleware([Authenticate::class])->group(function (): void {
    Route::get('/descuento', [RiderLookupController::class, 'discountForm'])->name('portal.discount.form');
    Route::post('/descuento', [RiderLookupController::class, 'discount'])->name('portal.discount');
    Route::get('/session/refresh', SessionRefreshController::class)->name('session.refresh');
});
Route::get('/documents/{document}/preview', UploadedDocumentPreviewController::class)
    ->middleware('signed')
    ->name('documents.preview');
Route::get('/documents/{document}/download', UploadedDocumentDownloadController::class)
    ->middleware('signed')
    ->name('documents.download');
