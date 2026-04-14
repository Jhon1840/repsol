<?php

namespace Database\Seeders;

use App\Models\ArticuloPointCost;
use App\Models\Articulos;
use Illuminate\Database\Seeder;

class ArticulosSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach ($this->articulos() as $articulo) {
            Articulos::query()->updateOrCreate(
                ['nombre' => $articulo['nombre']],
                ['descripcion' => $articulo['descripcion']],
            );
        }

        $articulos = Articulos::query()
            ->whereIn('nombre', array_keys($this->pointCosts()))
            ->pluck('id', 'nombre');

        foreach ($this->pointCosts() as $articleName => $costsByRango) {
            $articuloId = $articulos->get($articleName);

            if (! $articuloId) {
                continue;
            }

            foreach ($costsByRango as $rango => $points) {
                ArticuloPointCost::query()->updateOrCreate(
                    [
                        'articulo_id' => $articuloId,
                        'rango' => $rango,
                    ],
                    ['points' => $points],
                );
            }
        }
    }

    /**
     * @return array<int, array{nombre: string, descripcion: null}>
     */
    protected function articulos(): array
    {
        return [
            ['nombre' => 'Gorra', 'descripcion' => null],
            ['nombre' => 'Vaso Repsol', 'descripcion' => null],
            ['nombre' => 'Gorra modelo B', 'descripcion' => null],
            ['nombre' => 'Riñonera', 'descripcion' => null],
            ['nombre' => 'Portalápiz', 'descripcion' => null],
            ['nombre' => 'Llavero', 'descripcion' => null],
        ];
    }

    /**
     * @return array<string, array<string, int>>
     */
    protected function pointCosts(): array
    {
        return [
            'Gorra' => [
                'DIAMANTE' => 200,
                'ORO' => 300,
                'PLATA' => 400,
                'BRONCE' => 500,
            ],
            'Vaso Repsol' => [
                'DIAMANTE' => 500,
                'ORO' => 600,
                'PLATA' => 700,
                'BRONCE' => 800,
            ],
            'Gorra modelo B' => [
                'DIAMANTE' => 300,
                'ORO' => 400,
                'PLATA' => 500,
                'BRONCE' => 600,
            ],
            'Riñonera' => [
                'DIAMANTE' => 400,
                'ORO' => 500,
                'PLATA' => 600,
                'BRONCE' => 700,
            ],
            'Portalápiz' => [
                'DIAMANTE' => 300,
                'ORO' => 400,
                'PLATA' => 500,
                'BRONCE' => 600,
            ],
        ];
    }
}
