<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Articulos extends Model
{
    protected $table = 'articulos';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function pointCosts(): HasMany
    {
        return $this->hasMany(ArticuloPointCost::class, 'articulo_id');
    }
}
