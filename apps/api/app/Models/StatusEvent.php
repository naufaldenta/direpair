<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\StatusEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class StatusEvent extends Model
{
    /** @use HasFactory<StatusEventFactory> */
    use HasFactory;

    protected $fillable = [
        'service_request_id', 'actor_user_id', 'from_status', 'to_status',
        'public_label', 'public_message', 'visible_to_customer', 'metadata', 'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'visible_to_customer' => 'boolean',
            'metadata' => 'array',
            'occurred_at' => 'immutable_datetime',
        ];
    }

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
