<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

final class MidtransNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'order_id' => ['required', 'string', 'max:255'],
            'status_code' => ['required', 'string', 'max:8'],
            'gross_amount' => ['required', 'numeric', 'min:0'],
            'signature_key' => ['required_unless:transaction_status,mock-paid', 'nullable', 'string', 'max:255'],
            'transaction_status' => ['required', 'string', 'max:64'],
            'transaction_id' => ['nullable', 'string', 'max:255'],
            'fraud_status' => ['nullable', 'string', 'max:64'],
            'settlement_time' => ['nullable', 'date'],
            'transaction_time' => ['nullable', 'date'],
        ];
    }
}
