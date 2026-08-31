<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\QuotationStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class RepairStatusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        unset($request);
        $quotation = $this->quotations
            ->first(fn ($item): bool => in_array(
                $item->status,
                [QuotationStatus::Sent, QuotationStatus::Approved],
                true,
            ));

        return [
            'request_number' => $this->public_number,
            'device' => [
                'category' => $this->device_category,
                'brand' => $this->brand,
                'model' => $this->model,
            ],
            'service_method' => $this->preferred_service_method,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'payment_status' => $this->payment_status->value,
            'submitted_at' => $this->submitted_at->toAtomString(),
            'warranty_expires_at' => $this->warranty_expires_at?->toDateString(),
            'timeline' => $this->statusEvents->map(fn ($event): array => [
                'status' => $event->to_status,
                'label' => $event->public_label,
                'message' => $event->public_message,
                'occurred_at' => $event->occurred_at->toAtomString(),
            ])->values(),
            'quotation' => $quotation === null ? null : new QuotationResource($quotation),
        ];
    }
}
