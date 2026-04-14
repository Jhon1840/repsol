<?php

namespace Tests\Feature;

use App\Models\Rider;
use App\Models\RiderMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiderPointsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_rider_points_by_code(): void
    {
        $rider = Rider::query()->create([
            'rider_id' => 'SC00065',
            'name' => 'Sandra Parada',
            'branch' => 'CENTRAL',
            'rango' => 'DIAMANTE',
        ]);

        RiderMovement::query()->create([
            'rider_id' => $rider->getKey(),
            'movement_type' => 'purchase',
            'description' => 'Compra de prueba',
            'points' => 125,
            'occurred_at' => now(),
        ]);

        RiderMovement::query()->create([
            'rider_id' => $rider->getKey(),
            'movement_type' => 'points_redemption',
            'description' => 'Canje de puntos: Cambio de aceite',
            'points' => -25,
            'occurred_at' => now()->addMinute(),
            'metadata' => [
                'redemption_comment' => 'Cambio de aceite',
            ],
        ]);

        $this->getJson('/api/riders/sc00065/points')
            ->assertOk()
            ->assertHeader('Access-Control-Allow-Origin', '*')
            ->assertJson([
                'rider' => [
                    'rider_id' => 'PYASC00065',
                    'name' => 'Sandra Parada',
                    'branch' => 'CENTRAL',
                    'rango' => 'DIAMANTE',
                    'points_balance' => 100,
                ],
                'recent_movements' => [
                    [
                        'movement_type' => 'points_redemption',
                        'description' => 'Canje de puntos: Cambio de aceite',
                        'points' => -25,
                    ],
                ],
            ]);
    }

    public function test_it_returns_not_found_when_rider_does_not_exist(): void
    {
        $this->getJson('/api/riders/missing/points')
            ->assertNotFound()
            ->assertJson([
                'message' => 'No se encontro un rider con ese ID.',
            ]);
    }
}
