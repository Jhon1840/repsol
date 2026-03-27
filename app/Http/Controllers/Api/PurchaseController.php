<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PurchaseService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Throwable;

class PurchaseController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        Log::info('API purchase request received.', [
            'rider_code' => $request->input('rider_code'),
            'product_code' => $request->input('product_code'),
            'ip' => $request->ip(),
        ]);

        try {
            $payload = Validator::make($request->all(), [
                'rider_code' => ['required', 'string'],
                'product_code' => ['required', 'string'],
            ])->validate();

            $movement = app(PurchaseService::class)->createFromCodes(
                $payload['rider_code'],
                $payload['product_code'],
            );

            Log::info('API purchase created successfully.', [
                'movement_id' => $movement->getKey(),
                'rider_code' => $movement->rider?->rider_id,
                'product_code' => $movement->product?->code,
                'points' => $movement->points,
            ]);

            return response()->json([
                'id' => $movement->getKey(),
                'rider_code' => $movement->rider?->rider_id,
                'product_code' => $movement->product?->code,
                'points' => $movement->points,
                'occurred_at' => $movement->occurred_at?->toISOString(),
            ], 201);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (QueryException $exception) {
            Log::error('API purchase database error.', [
                'message' => $exception->getMessage(),
                'rider_code' => $request->input('rider_code'),
                'product_code' => $request->input('product_code'),
            ]);

            throw $exception;
        } catch (Throwable $exception) {
            Log::error('API purchase unexpected error.', [
                'message' => $exception->getMessage(),
                'rider_code' => $request->input('rider_code'),
                'product_code' => $request->input('product_code'),
            ]);

            throw $exception;
        }
    }
}
