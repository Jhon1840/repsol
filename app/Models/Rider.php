<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Rider extends Model
{
    protected $fillable = [
        'rider_id',
        'name',
    ];

    public function movements(): HasMany
    {
        return $this->hasMany(RiderMovement::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(UploadedDocument::class);
    }

    public function getRouteKeyName(): string
    {
        return 'rider_id';
    }

    public function scopeWithPointsBalance(Builder $query): Builder
    {
        return $query->withSum('movements as points_balance', 'points');
    }

    protected function pointsBalance(): Attribute
    {
        return Attribute::make(
            get: fn (?int $value, array $attributes): int => (int) ($attributes['points_balance'] ?? $this->movements()->sum('points')),
        );
    }
}
