<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid', 'name', 'phone', 'email', 'whatsapp_consent', 'privacy_consent_at', 'privacy_consent_version',
    ];

    protected function casts(): array
    {
        return [
            'whatsapp_consent' => 'boolean',
            'privacy_consent_at' => 'immutable_datetime',
        ];
    }

    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }
}
