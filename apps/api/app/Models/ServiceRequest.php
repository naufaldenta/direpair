<?php

declare(strict_types=1);

namespace App\Models;

use App\PaymentStatus;
use App\RepairStatus;
use Database\Factories\ServiceRequestFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ServiceRequest extends Model
{
    /** @use HasFactory<ServiceRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid', 'customer_id', 'public_number', 'public_token_hash', 'service_slug',
        'device_category', 'brand', 'model', 'serial_number', 'symptom',
        'preferred_service_method', 'service_address', 'urgency', 'status',
        'payment_status', 'source', 'is_demo', 'submitted_at', 'warranty_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'service_address' => 'array',
            'status' => RepairStatus::class,
            'payment_status' => PaymentStatus::class,
            'is_demo' => 'boolean',
            'submitted_at' => 'immutable_datetime',
            'warranty_expires_at' => 'immutable_datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(ServiceRequestMedia::class);
    }

    public function diagnoses(): HasMany
    {
        return $this->hasMany(Diagnosis::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function statusEvents(): HasMany
    {
        return $this->hasMany(StatusEvent::class);
    }

    public function scopeReal(Builder $query): Builder
    {
        return $query->where('is_demo', false);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
