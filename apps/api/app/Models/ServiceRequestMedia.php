<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ServiceRequestMediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ServiceRequestMedia extends Model
{
    /** @use HasFactory<ServiceRequestMediaFactory> */
    use HasFactory;

    protected $fillable = [
        'service_request_id', 'disk', 'path', 'original_name', 'mime_type', 'size_bytes', 'is_demo',
    ];

    protected function casts(): array
    {
        return ['is_demo' => 'boolean'];
    }

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }
}
