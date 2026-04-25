<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UploadedDocument extends Model
{
    protected $fillable = [
        'rider_id',
        'uploaded_by',
        'original_name',
        'path',
        'disk',
        'mime_type',
        'size',
        'status',
        'uploaded_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'uploaded_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(Rider::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(RiderMovement::class, 'uploaded_document_id');
    }

    public function scopeWithImportErrors(Builder $query): Builder
    {
        return $query->whereIn('status', [
            'processed_with_errors',
            'processed_without_points',
            'failed',
        ]);
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if (! $branch = $user?->branchScope()) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($branch): void {
            $query
                ->whereHas('rider', fn (Builder $query): Builder => $query->where('branch', $branch))
                ->orWhere('metadata->branch_scope', $branch);
        });
    }

    public function hasImportErrors(): bool
    {
        return $this->importErrorCount() > 0 || $this->status === 'failed';
    }

    public function importErrorCount(): int
    {
        return count($this->getSkippedItems()) + count($this->getFatalErrors());
    }

    public function getSkippedItems(): array
    {
        return data_get($this->metadata, 'skipped_items', []);
    }

    public function getFatalErrors(): array
    {
        return data_get($this->metadata, 'fatal_errors', []);
    }

    public function getImportErrors(): array
    {
        return [
            'skipped_items' => $this->getSkippedItems(),
            'fatal_errors' => $this->getFatalErrors(),
        ];
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'processed_with_errors' => 'Procesado con errores',
            'processed_without_points' => 'Sin puntos procesables',
            'failed' => 'Fallido',
            'processed' => 'Procesado',
            'pending_assignment' => 'Pendiente',
            default => str_replace('_', ' ', (string) $this->status),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'processed_with_errors' => 'warning',
            'processed_without_points' => 'info',
            'failed' => 'danger',
            'processed' => 'success',
            default => 'gray',
        };
    }

    public function branchLabel(): string
    {
        return $this->rider?->branch
            ?? data_get($this->metadata, 'branch_scope')
            ?? data_get($this->metadata, 'parsed_riders.0.branch')
            ?? data_get($this->metadata, 'skipped_items.0.branch')
            ?? 'Sin sucursal';
    }
}
