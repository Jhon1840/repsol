<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticuloPointCost extends Model
{
    protected $fillable = [
        'articulo_id',
        'rango',
        'points',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
        ];
    }

    public function articulo(): BelongsTo
    {
        return $this->belongsTo(Articulos::class, 'articulo_id');
    }
}
