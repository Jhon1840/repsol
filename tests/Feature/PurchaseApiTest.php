<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Rider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_purchase_from_rider_and_product_codes(): void
    {
        $rider = Rider::query()->create([
            'rider_id' => 'SC00065',
            'name' => 'Sandra Parada',
        ]);

        $product = Product::query()->create([
            'name' => 'Aceite 4T',
            'code' => 'RPP2065THC',
            'liters' => 12,
            'points_per_box' => 2400,
            'points_per_liter' => 200,
        ]);

        $response = $this->postJson('/api/purchases', [
            'rider_code' => $rider->rider_id,
            'product_code' => $product->code,
        ]);

        $response
            ->assertCreated()
            ->assertJson([
                'rider_code' => 'PYASC00065',
                'product_code' => 'RPP2065THC',
                'points' => 2400,
            ]);

        $this->assertDatabaseHas('rider_movements', [
            'rider_id' => $rider->getKey(),
            'product_id' => $product->getKey(),
            'movement_type' => 'purchase',
            'points' => 2400,
            'description' => 'RPP2065THC Aceite 4T',
        ]);

        $movement = $rider->movements()->firstOrFail();

        $this->assertSame('api_purchase', $movement->metadata['source']);
        $this->assertSame('PYASC00065', $movement->metadata['rider_code']);
        $this->assertSame('RPP2065THC', $movement->metadata['product_code']);

        $rider->loadSum('movements as points_balance', 'points');

        $this->assertSame(2400, $rider->points_balance);
    }

    public function test_it_returns_validation_error_when_rider_code_does_not_exist(): void
    {
        Product::query()->create([
            'name' => 'Aceite 4T',
            'code' => 'RPP2065THC',
            'liters' => 12,
            'points_per_box' => 2400,
            'points_per_liter' => 200,
        ]);

        $response = $this->postJson('/api/purchases', [
            'rider_code' => 'MISSING',
            'product_code' => 'RPP2065THC',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['rider_code']);
    }

    public function test_it_returns_validation_error_when_product_code_does_not_exist(): void
    {
        Rider::query()->create([
            'rider_id' => 'SC00065',
            'name' => 'Sandra Parada',
        ]);

        $response = $this->postJson('/api/purchases', [
            'rider_code' => 'SC00065',
            'product_code' => 'MISSING',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['product_code']);
    }

    public function test_product_code_must_be_unique_and_liters_are_cast_as_decimal(): void
    {
        $product = Product::query()->create([
            'name' => 'Producto Demo',
            'code' => 'PROD-001',
            'liters' => 1.5,
            'points_per_box' => 300,
            'points_per_liter' => 200,
        ])->fresh();

        $this->assertSame('1.50', $product->liters);
        $this->assertSame('300.00', $product->points_per_box);
        $this->assertSame('200.00', $product->points_per_liter);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Product::query()->create([
            'name' => 'Producto Duplicado',
            'code' => 'PROD-001',
            'liters' => 2,
            'points_per_box' => 400,
            'points_per_liter' => 200,
        ]);
    }
}
