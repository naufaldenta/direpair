<?php

declare(strict_types=1);

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'diagnosis_summary' => ['required', 'string', 'min:10', 'max:3000'],
            'repairability' => ['required', Rule::in(['repairable', 'not_repairable', 'needs_parts'])],
            'findings' => ['nullable', 'string', 'max:3000'],
            'estimated_days' => ['nullable', 'integer', 'min:1', 'max:180'],
            'internal_notes' => ['nullable', 'string', 'max:3000'],
            'items' => ['required', 'array', 'min:1', 'max:30'],
            'items.*.type' => ['required', Rule::in(['diagnosis', 'labor', 'part', 'shipping', 'other'])],
            'items.*.label' => ['required', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string', 'max:1000'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01', 'max:9999'],
            'items.*.unit_price' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'discount' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'tax' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'deposit_required' => ['nullable', 'boolean'],
            'deposit_amount' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'customer_notes' => ['nullable', 'string', 'max:2000'],
            'valid_days' => ['nullable', 'integer', 'min:1', 'max:90'],
        ];
    }
}
