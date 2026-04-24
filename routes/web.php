<?php

use App\Http\Controllers\SessionRefreshController;
use App\Http\Controllers\UploadedDocumentDownloadController;
use App\Http\Controllers\UploadedDocumentPreviewController;
use App\Http\Controllers\RiderLookupController;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => to_route('filament.admin.auth.login'));
Route::get('/consulta-puntos/', [RiderLookupController::class, 'index'])->name('portal.index');
Route::post('/consulta-puntos/', [RiderLookupController::class, 'search'])->name('portal.search');
Route::get('/consulta-puntos/{rider}/premios', [RiderLookupController::class, 'rewards'])->name('portal.rewards');
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
