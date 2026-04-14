<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RiderPointsController extends Controller
{
    public function show(Request $request, string $riderId): JsonResponse
    {
        $normalizedRiderId = Rider::normalizeRiderId($riderId);

        if ($normalizedRiderId === null) {
            return $this->corsResponse([
                'message' => 'El ID de rider es obligatorio.',
            ], 422);
        }

        $rider = Rider::query()
            ->with(['movements' => fn ($query) => $query->latest('occurred_at')->limit(5)])
            ->withPointsBalance()
            ->where('rider_id', $normalizedRiderId)
            ->first();

        if (! $rider) {
            return $this->corsResponse([
                'message' => 'No se encontro un rider con ese ID.',
            ], 404);
        }

        return $this->corsResponse([
            'rider' => [
                'rider_id' => $rider->rider_id,
                'name' => $rider->name,
                'branch' => $rider->branch,
                'rango' => $rider->rango,
                'points_balance' => (int) $rider->points_balance,
            ],
            'recent_movements' => $rider->movements
                ->map(fn ($movement): array => [
                    'movement_type' => $movement->movement_type,
                    'description' => data_get($movement->metadata, 'source') === 'excel_auto_import'
                        ? 'Puntos agregados por su compra'
                        : ($movement->description ?? ucfirst($movement->movement_type)),
                    'points' => (int) $movement->points,
                    'occurred_at' => $movement->occurred_at?->toDateString(),
                ])
                ->values(),
        ]);
    }

    public function options(): JsonResponse
    {
        return $this->corsResponse([], 204);
    }

    protected function corsResponse(array $payload, int $status = 200): JsonResponse
    {
        return response()
            ->json($payload, $status)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Accept');
    }
}
