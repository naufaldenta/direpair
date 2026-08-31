<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\RepairStatusResource;
use App\Models\ServiceRequest;

final class RepairStatusController extends Controller
{
    public function __invoke(string $token): RepairStatusResource
    {
        abort_if(strlen($token) < 32, 404);
        $hash = hash_hmac('sha256', $token, (string) config('direpair.token_pepper'));
        $serviceRequest = ServiceRequest::query()
            ->where('public_token_hash', $hash)
            ->with([
                'statusEvents' => fn ($query) => $query
                    ->where('visible_to_customer', true)
                    ->oldest('occurred_at'),
                'quotations' => fn ($query) => $query
                    ->with(['diagnosis', 'items', 'invoices.payments'])
                    ->latest('version'),
            ])
            ->firstOrFail();

        return new RepairStatusResource($serviceRequest);
    }
}
