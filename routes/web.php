<?php

use App\Http\Controllers\RiderLookupController;
use Illuminate\Support\Facades\Route;

Route::get('/', [RiderLookupController::class, 'index'])->name('portal.index');
Route::post('/consulta', [RiderLookupController::class, 'search'])->name('portal.search');
Route::get('/consulta/{rider:rider_id}', [RiderLookupController::class, 'show'])->name('portal.show');
