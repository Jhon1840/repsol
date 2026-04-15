<?php

namespace Database\Seeders;

use App\Models\ArticuloPointCost;
use App\Models\Articulos;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArticulosSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            foreach ($this->articulos() as $articulo) {
                $this->seedArticulo($articulo);
            }

            foreach ($this->pointCosts() as $articleName => $costsByRango) {
                $articulo = $this->seedArticulo([
                    'nombre' => $articleName,
                    'descripcion' => null,
                ]);

                foreach ($costsByRango as $rango => $points) {
                    ArticuloPointCost::query()->updateOrCreate(
                        [
                            'articulo_id' => $articulo->id,
                            'rango' => $rango,
                        ],
                        ['points' => $points],
                    );
                }
            }
        });
    }

    /**
     * @param  array{nombre: string, descripcion: ?string}  $data
     */
    protected function seedArticulo(array $data): Articulos
    {
        $articulos = Articulos::query()
            ->where('nombre', $data['nombre'])
            ->orderBy('id')
            ->get();

        $articulo = $articulos->first();

        if (! $articulo) {
            return Articulos::query()->create($data);
        }

        $articulo->fill([
            'descripcion' => $data['descripcion'] ?? $articulo->descripcion,
        ])->save();

        foreach ($articulos->skip(1) as $duplicate) {
            foreach ($duplicate->pointCosts as $pointCost) {
                ArticuloPointCost::query()->updateOrCreate(
                    [
                        'articulo_id' => $articulo->id,
                        'rango' => $pointCost->rango,
                    ],
                    ['points' => $pointCost->points],
                );
            }

            $duplicate->delete();
        }

        return $articulo;
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
