<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'code',
        'liters',
    ];

    protected function casts(): array
    {
        return [
            'liters' => 'decimal:2',
        ];
    }

    public function movements(): HasMany
    {
        return $this->hasMany(RiderMovement::class);
    }
}
