<?php

use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\RiderPointsController;
use Illuminate\Support\Facades\Route;

Route::post('/purchases', PurchaseController::class);
Route::get('/riders/{riderId}/points', [RiderPointsController::class, 'show']);
Route::options('/riders/{riderId}/points',  [RiderPointsController::class, 'options']);
