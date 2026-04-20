<?php

namespace Tests\Feature;

use App\Models\ArticuloPointCost;
use App\Models\Articulos;
use App\Models\Rider;
use App\Models\RiderMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalRewardsTest extends TestCase
{
    use RefreshDatabase;

    public function test_rider_can_open_rewards_screen_from_points_result(): void
    {
        $rider = Rider::query()->create([
            'rider_id' => 'PYA12345',
            'name' => 'Rider Premios',
            'rango' => 'PLATA',
        ]);

        $this->withSession(['_token' => 'test-token'])
            ->post(route('portal.search'), [
                '_token' => 'test-token',
                'rider_id' => 'PYA12345',
            ])
            ->assertOk()
            ->assertSee('Ver premios disponibles')
            ->assertSee(route('portal.rewards', $rider), false);
    }

    public function test_rewards_screen_shows_article_costs_for_rider_rango(): void
    {
        $rider = Rider::query()->create([
            'rider_id' => 'PYA67890',
            'name' => 'Rider Con Premios',
            'rango' => 'ORO',
        ]);

        RiderMovement::query()->create([
            'rider_id' => $rider->getKey(),
            'movement_type' => 'purchase',
            'points' => 450,
            'occurred_at' => now(),
        ]);

        $gorra = Articulos::query()->create([
            'nombre' => 'Gorra',
            'descripcion' => 'Gorra oficial del programa.',
            'imagenes' => 'articulos/gorra.png',
        ]);

        $vaso = Articulos::query()->create([
            'nombre' => 'Vaso Repsol',
        ]);

        ArticuloPointCost::query()->create([
            'articulo_id' => $gorra->getKey(),
            'rango' => 'ORO',
            'points' => 300,
        ]);

        ArticuloPointCost::query()->create([
            'articulo_id' => $vaso->getKey(),
            'rango' => 'ORO',
            'points' => 700,
        ]);

        ArticuloPointCost::query()->create([
            'articulo_id' => $vaso->getKey(),
            'rango' => 'PLATA',
            'points' => 100,
        ]);

        $this->get(route('portal.rewards', $rider))
            ->assertOk()
            ->assertSee('Premios disponibles')
            ->assertSee('450')
            ->assertSee('ORO')
            ->assertSee('Gorra')
            ->assertSee('storage/articulos/gorra.png')
            ->assertSee('300')
            ->assertSee('Te alcanza')
            ->assertSee('Vaso Repsol')
            ->assertSee('700')
            ->assertSee('Faltan 250')
            ->assertDontSee('>100</strong>', false);
    }
}
