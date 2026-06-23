<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rider extends Model
{
    public const RIDER_ID_PREFIX = 'PYA';

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

    protected array $auditOriginalValues = [];

    public function movements(): HasMany
    {
        return $this->hasMany(RiderMovement::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(RiderAuditLog::class)->latest('occurred_at');
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

    public static function normalizeRiderId(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = strtoupper(trim((string) $value));

        if ($normalized === '') {
            return null;
        }

        if (str_starts_with($normalized, self::RIDER_ID_PREFIX)) {
            return $normalized;
        }

        if (str_starts_with($normalized, 'PY')) {
            return self::RIDER_ID_PREFIX.substr($normalized, 2);
        }

        return self::RIDER_ID_PREFIX.$normalized;
    }

    public static function riderIdSuffix(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = strtoupper(trim((string) $value));

        if ($normalized === '') {
            return null;
        }

        if (str_starts_with($normalized, self::RIDER_ID_PREFIX)) {
            return substr($normalized, strlen(self::RIDER_ID_PREFIX)) ?: null;
        }

        if (str_starts_with($normalized, 'PY')) {
            return substr($normalized, 2) ?: null;
        }

        return $normalized;
    }

    public function scopeWithPointsBalance(Builder $query, ?User $user = null): Builder
    {
        return $query->withSum('movements as points_balance', 'points');
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if ($user?->isAdvisor()) {
            return $query->where('created_by', $user->getKey());
        }

        if (! $branch = $user?->branchScope()) {
            return $query;
        }

        return $query->where('branch', $branch);
    }

    public function creationSourceLabel(): string
    {
        if ($this->creation_source === 'excel') {
            return 'Excel';
        }

        if ($this->created_by === null) {
            return 'Sistema';
        }

        return 'Manual';
    }

    protected function pointsBalance(): Attribute
    {
        return Attribute::make(
            get: fn (?int $value, array $attributes): int => (int) ($attributes['points_balance'] ?? $this->movements()->sum('points')),
        );
    }

    protected function riderId(): Attribute
    {
        return Attribute::make(
            set: fn (mixed $value): ?string => self::normalizeRiderId($value),
        );
    }

    protected static function booted(): void
    {
        static::created(function (Rider $rider): void {
            $rider->writeAuditLog('created', [], $rider->auditSnapshot());
        });

        static::updating(function (Rider $rider): void {
            $rider->auditOriginalValues = collect($rider->getDirty())
                ->keys()
                ->intersect($rider->auditedAttributes())
                ->mapWithKeys(fn (string $attribute): array => [$attribute => $rider->getOriginal($attribute)])
                ->all();
        });

        static::updated(function (Rider $rider): void {
            if ($rider->auditOriginalValues === []) {
                return;
            }

            $newValues = collect($rider->auditOriginalValues)
                ->keys()
                ->mapWithKeys(fn (string $attribute): array => [$attribute => $rider->getAttribute($attribute)])
                ->all();

            $rider->writeAuditLog('updated', $rider->auditOriginalValues, $newValues);
            $rider->auditOriginalValues = [];
        });

        static::deleting(function (Rider $rider): void {
            $rider->writeAuditLog('deleted', $rider->auditSnapshot(), []);
        });
    }

    protected function auditedAttributes(): array
    {
        return [
            'rider_id',
            'name',
            'branch',
            'rango',
            'created_by',
            'updated_by',
            'creation_source',
        ];
    }

    protected function auditSnapshot(): array
    {
        return collect($this->only($this->auditedAttributes()))
            ->filter(fn (mixed $value): bool => $value !== null)
            ->all();
    }

    protected function writeAuditLog(string $event, array $oldValues, array $newValues): void
    {
        RiderAuditLog::query()->create([
            'rider_id' => $this->getKey(),
            'rider_code' => $this->rider_id,
            'rider_name' => $this->name,
            'user_id' => auth()->id(),
            'event' => $event,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'url' => request()?->fullUrl(),
            'method' => request()?->method(),
            'occurred_at' => now(),
        ]);
    }
}
