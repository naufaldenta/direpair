<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class QuotationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        unset($request);
        $invoice = $this->whenLoaded('invoices')->sortByDesc('id')->first();
        $payment = $invoice?->payments?->sortByDesc('id')->first();

        return [
            'id' => $this->uuid,
            'version' => $this->version,
            'status' => $this->status->value,
            'currency' => $this->currency,
            'subtotal' => $this->subtotal,
            'discount' => $this->discount,
            'tax' => $this->tax,
            'total' => $this->total,
            'deposit_required' => $this->deposit_required,
            'deposit_amount' => $this->deposit_amount,
            'customer_notes' => $this->customer_notes,
            'expires_at' => $this->expires_at->toAtomString(),
            'approved_at' => $this->approved_at?->toAtomString(),
            'diagnosis' => $this->whenLoaded('diagnosis', fn (): array => [
                'repairability' => $this->diagnosis?->repairability,
                'summary' => $this->diagnosis?->summary,
                'estimated_days' => $this->diagnosis?->estimated_days,
            ]),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item): array => [
                'type' => $item->type,
                'label' => $item->label,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total' => $item->total,
            ])->values()),
            'invoice' => $invoice === null ? null : [
                'number' => $invoice->number,
                'kind' => $invoice->kind,
                'status' => $invoice->status->value,
                'amount' => $invoice->amount,
                'paid_amount' => $invoice->paid_amount,
                'payment_status' => $payment?->status?->value,
                'checkout_url' => $payment?->checkout_url,
            ],
        ];
    }
}
