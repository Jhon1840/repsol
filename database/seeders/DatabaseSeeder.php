<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@repsol-filament.test'],
            [
                'name' => 'Administrador Repsol',
                'password' => 'password',
            ],
        );

        $riders = [
            ['rider_id' => 'SC00065', 'name' => 'SANDRA PARADA CABALLERO'],
            ['rider_id' => 'SC00081', 'name' => 'JORGE MAMANI FLORES'],
            ['rider_id' => 'SC00102', 'name' => 'MARIA QUISPE ROJAS'],
        ];

        foreach ($riders as $index => $data) {
            $rider = \App\Models\Rider::query()->updateOrCreate(
                ['rider_id' => $data['rider_id']],
                ['name' => $data['name']],
            );

            if ($rider->movements()->exists()) {
                continue;
            }

            $rider->movements()->createMany([
                [
                    'movement_type' => 'purchase',
                    'reference' => 'FAC-' . (4100 + $index),
                    'description' => 'Compra de lubricantes',
                    'amount' => 350.00 + ($index * 25),
                    'points' => 35 + ($index * 5),
                    'occurred_at' => now()->subDays(10 - $index),
                ],
                [
                    'movement_type' => 'purchase',
                    'reference' => 'FAC-' . (4200 + $index),
                    'description' => 'Compra de repuestos',
                    'amount' => 180.00 + ($index * 10),
                    'points' => 18 + ($index * 3),
                    'occurred_at' => now()->subDays(3 - $index),
                ],
            ]);
        }
    }
}
