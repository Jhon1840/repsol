<?php

namespace Tests\Unit;

use App\Models\ArticuloPointCost;
use App\Models\Articulos;
use Database\Seeders\ArticulosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticulosSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_articulos_seeder_completes_missing_point_costs_without_duplicates(): void
    {
        Articulos::query()->create([
            'nombre' => 'Gorra',
            'descripcion' => 'Articulo existente',
        ]);

        Articulos::query()->create([
            'nombre' => 'Gorra',
            'descripcion' => 'Duplicado anterior',
        ]);

        $this->seed(ArticulosSeeder::class);
        $this->seed(ArticulosSeeder::class);

        $this->assertSame(1, Articulos::query()->where('nombre', 'Gorra')->count());
        $this->assertSame(6, Articulos::query()->count());
        $this->assertSame(20, ArticuloPointCost::query()->count());

        $gorra = Articulos::query()->where('nombre', 'Gorra')->firstOrFail();

        $this->assertDatabaseHas('articulo_point_costs', [
            'articulo_id' => $gorra->id,
            'rango' => 'DIAMANTE',
            'points' => 200,
        ]);

        $this->assertDatabaseHas('articulo_point_costs', [
            'articulo_id' => $gorra->id,
            'rango' => 'BRONCE',
            'points' => 500,
        ]);
    }
}
