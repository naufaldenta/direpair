<?php

declare(strict_types=1);

namespace App\Models;

use App\PaymentStatus;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid', 'invoice_id', 'gateway', 'external_order_id', 'gateway_transaction_id',
        'status', 'currency', 'amount', 'gateway_status', 'fraud_status', 'checkout_token',
        'checkout_url', 'metadata', 'paid_at', 'last_notified_at',
    ];

    protected $hidden = ['checkout_token'];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'metadata' => 'array',
            'paid_at' => 'immutable_datetime',
            'last_notified_at' => 'immutable_datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
