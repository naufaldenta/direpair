<?php

declare(strict_types=1);

namespace App\Models;

use App\QuotationStatus;
use Database\Factories\QuotationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Quotation extends Model
{
    /** @use HasFactory<QuotationFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid', 'service_request_id', 'diagnosis_id', 'created_by', 'version',
        'status', 'currency', 'subtotal', 'discount', 'tax', 'total',
        'deposit_required', 'deposit_amount', 'customer_notes', 'sent_at',
        'expires_at', 'approved_at', 'approval_ip_hash',
    ];

    protected function casts(): array
    {
        return [
            'status' => QuotationStatus::class,
            'deposit_required' => 'boolean',
            'sent_at' => 'immutable_datetime',
            'expires_at' => 'immutable_datetime',
            'approved_at' => 'immutable_datetime',
        ];
    }

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function diagnosis(): BelongsTo
    {
        return $this->belongsTo(Diagnosis::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class)->orderBy('sort_order');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
