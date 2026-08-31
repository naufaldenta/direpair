<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ServiceRequestCreatedResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        unset($request);
        $serviceRequest = $this->resource['service_request'];
        $statusToken = (string) $this->resource['status_token'];

        return [
            'request_number' => $serviceRequest->public_number,
            'status' => $serviceRequest->status->value,
            'status_label' => $serviceRequest->status->label(),
            'status_token' => $statusToken,
            'status_url' => rtrim((string) config('direpair.public_status_url'), '/').'/'.$statusToken,
            'submitted_at' => $serviceRequest->submitted_at->toAtomString(),
            'next_step' => $serviceRequest->preferred_service_method === 'drop-off'
                ? 'Bawa unit setelah tim Direpair mengonfirmasi jadwal dan lokasi.'
                : 'Tim Direpair akan mengonfirmasi area dan jadwal layanan.',
        ];
    }
}
