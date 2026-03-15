<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

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
}
