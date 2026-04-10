<?php

use App\Http\Controllers\SessionRefreshController;
use App\Http\Controllers\UploadedDocumentPreviewController;
use App\Http\Controllers\RiderLookupController;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;

Route::get('/', [RiderLookupController::class, 'index'])->name('portal.index');
Route::post('/consulta', [RiderLookupController::class, 'search'])->name('portal.search');
Route::get('/consulta/{rider:rider_id}', [RiderLookupController::class, 'show'])->name('portal.show');
Route::middleware([Authenticate::class])->group(function (): void {
    Route::get('/descuento', [RiderLookupController::class, 'discountForm'])->name('portal.discount.form');
    Route::post('/descuento', [RiderLookupController::class, 'discount'])->name('portal.discount');
    Route::get('/session/refresh', SessionRefreshController::class)->name('session.refresh');
});
Route::get('/documents/{document}/preview', UploadedDocumentPreviewController::class)
    ->middleware('signed')
    ->name('documents.preview');
