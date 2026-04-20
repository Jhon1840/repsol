<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Articulos extends Model
{
    protected $table = 'articulos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'imagenes',
    ];

    protected $casts = [
        'imagenes' => 'array',
    ];

    public function pointCosts(): HasMany
    {
        return $this->hasMany(ArticuloPointCost::class, 'articulo_id');
    }

    public function primaryImagePath(): ?string
    {
        $imagenes = is_array($this->imagenes)
            ? $this->imagenes
            : [$this->imagenes];

        return collect($imagenes)
            ->filter(fn (?string $path): bool => filled($path))
            ->first();
    }

    public function primaryImageUrl(): ?string
    {
        $path = $this->primaryImagePath();

        return $path ? Storage::disk('public')->url($path) : null;
    }
}
