<?php

namespace App\Filament\Resources\Articulos\Pages\Concerns;

use App\Models\Rider;

trait ManagesArticuloPointCosts
{
    protected array $pointCosts = [];

    protected function extractPointCosts(array &$data): void
    {
        $this->pointCosts = $data['point_costs'] ?? [];

        unset($data['point_costs']);
    }

    protected function fillPointCosts(array $data): array
    {
        $costs = $this->getRecord()
            ->pointCosts()
            ->pluck('points', 'rango')
            ->all();

        $data['point_costs'] = collect(Rider::RANGO_OPTIONS)
            ->mapWithKeys(fn (string $label, string $rango): array => [$rango => $costs[$rango] ?? null])
            ->all();

        return $data;
    }

    protected function savePointCosts(): void
    {
        foreach (Rider::RANGO_OPTIONS as $rango => $label) {
            $this->getRecord()
                ->pointCosts()
                ->updateOrCreate(
                    ['rango' => $rango],
                    ['points' => (int) ($this->pointCosts[$rango] ?? 0)],
                );
        }
    }
}
