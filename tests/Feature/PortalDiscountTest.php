<?php

namespace Tests\Feature;

use App\Models\ArticuloPointCost;
use App\Models\Articulos;
use App\Models\Rider;
use App\Models\RiderMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalDiscountTest extends TestCase
{
    use RefreshDatabase;

    public function test_discount_form_shows_article_costs_for_rider_rango(): void
    {
        $user = User::query()->create([
            'name' => 'Admin',
            'email' => 'admin-form@example.com',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
        ]);

        Rider::query()->create([
            'rider_id' => 'SC00064',
            'name' => 'Rider Form',
            'rango' => 'ORO',
        ]);

        $gorra = Articulos::query()->create(['nombre' => 'Gorra']);

        ArticuloPointCost::query()->create([
            'articulo_id' => $gorra->getKey(),
            'rango' => 'ORO',
            'points' => 300,
        ]);

        $this->actingAs($user)
            ->get(route('portal.discount.form', ['rider_id' => 'SC00064']))
            ->assertOk()
            ->assertSee('300 puntos c/u')
            ->assertSee('Total a descontar')
            ->assertDontSee('name="points"', false);
    }

    public function test_it_calculates_discount_points_from_rider_rango_and_selected_articles(): void
    {
        $user = User::query()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
        ]);

        $rider = Rider::query()->create([
            'rider_id' => 'SC00065',
            'name' => 'Sandra Parada',
            'branch' => 'CENTRAL',
            'rango' => 'PLATA',
        ]);

        RiderMovement::query()->create([
            'rider_id' => $rider->getKey(),
            'movement_type' => 'purchase',
            'points' => 2000,
            'occurred_at' => now(),
        ]);

        $gorra = Articulos::query()->create(['nombre' => 'Gorra']);
        $gorraModeloB = Articulos::query()->create(['nombre' => 'Gorra modelo B']);

        ArticuloPointCost::query()->create([
            'articulo_id' => $gorra->getKey(),
            'rango' => 'PLATA',
            'points' => 400,
        ]);

        ArticuloPointCost::query()->create([
            'articulo_id' => $gorraModeloB->getKey(),
            'rango' => 'PLATA',
            'points' => 500,
        ]);

        $this->actingAs($user)
            ->withSession(['_token' => 'test-token'])
            ->post(route('portal.discount'), [
                '_token' => 'test-token',
                'rider_id' => 'SC00065',
                'articulos' => [
                    $gorra->getKey() => 2,
                    $gorraModeloB->getKey() => 1,
                ],
            ])
            ->assertRedirect();

        $movement = RiderMovement::query()
            ->where('movement_type', 'points_redemption')
            ->firstOrFail();

        $this->assertSame(-1300, $movement->points);
        $this->assertSame('PLATA', $movement->metadata['rango']);
        $this->assertSame(1300, $movement->metadata['discounted_points']);
        $this->assertSame([
            (string) $gorra->getKey() => 800,
            (string) $gorraModeloB->getKey() => 500,
        ], $movement->metadata['selected_article_points_subtotals']);
    }

    public function test_it_requires_rider_rango_to_calculate_discount_points(): void
    {
        $user = User::query()->create([
            'name' => 'Admin',
            'email' => 'admin-no-rango@example.com',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
        ]);

        $rider = Rider::query()->create([
            'rider_id' => 'SC00066',
            'name' => 'Rider Sin Rango',
        ]);

        RiderMovement::query()->create([
            'rider_id' => $rider->getKey(),
            'movement_type' => 'purchase',
            'points' => 2000,
            'occurred_at' => now(),
        ]);

        $gorra = Articulos::query()->create(['nombre' => 'Gorra']);

        $this->actingAs($user)
            ->withSession(['_token' => 'test-token'])
            ->from(route('portal.discount.form', ['rider_id' => 'SC00066']))
            ->post(route('portal.discount'), [
                '_token' => 'test-token',
                'rider_id' => 'SC00066',
                'articulos' => [
                    $gorra->getKey() => 1,
                ],
            ])
            ->assertRedirect(route('portal.discount.form', ['rider_id' => 'SC00066']))
            ->assertSessionHasErrors('articulos');

        $this->assertDatabaseMissing('rider_movements', [
            'rider_id' => $rider->getKey(),
            'movement_type' => 'points_redemption',
        ]);
    }
}
