<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rider extends Model
{
    public const RANGO_OPTIONS = [
        'DIAMANTE' => 'Diamante',
        'BRONCE' => 'Bronce',
        'PLATA' => 'Plata',
        'ORO' => 'Oro',
    ];

    protected $fillable = [
        'rider_id',
        'name',
        'branch',
        'rango',
        'created_by',
        'updated_by',
        'creation_source',
    ];

    public function movements(): HasMany
    {
        return $this->hasMany(RiderMovement::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(UploadedDocument::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getRouteKeyName(): string
    {
        return 'rider_id';
    }

    public function scopeWithPointsBalance(Builder $query, ?User $user = null): Builder
    {
        return $query->withSum('movements as points_balance', 'points');
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if (! $branch = $user?->branchScope()) {
            return $query;
        }

        return $query->where('branch', $branch);
    }

    protected function pointsBalance(): Attribute
    {
        return Attribute::make(
            get: fn (?int $value, array $attributes): int => (int) ($attributes['points_balance'] ?? $this->movements()->sum('points')),
        );
    }
}
