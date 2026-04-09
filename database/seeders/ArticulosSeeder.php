<?php

namespace Database\Seeders;

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
}
