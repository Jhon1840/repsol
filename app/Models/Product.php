<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'code',
        'oil_type',
        'liters',
        'points_per_box',
        'points_per_liter',
    ];

    protected function casts(): array
    {
        return [
            'liters' => 'decimal:2',
            'points_per_box' => 'decimal:2',
            'points_per_liter' => 'decimal:2',
        ];
    }

    public function movements(): HasMany
    {
        return $this->hasMany(RiderMovement::class);
    }
}
