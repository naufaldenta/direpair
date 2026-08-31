<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CreateServiceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9\s\-()]{8,24}$/'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'service_slug' => ['required', 'string', 'alpha_dash:ascii', 'max:120'],
            'device_category' => ['required', 'string', 'max:120'],
            'brand' => ['nullable', 'string', 'max:120'],
            'model' => ['nullable', 'string', 'max:120'],
            'serial_number' => ['nullable', 'string', 'max:120'],
            'symptom' => ['required', 'string', 'min:10', 'max:2000'],
            'preferred_service_method' => ['required', Rule::in(['drop-off', 'pickup', 'home-service'])],
            'service_address' => ['nullable', 'array:street,city,region,postal_code,notes'],
            'service_address.street' => ['required_if:preferred_service_method,pickup,home-service', 'string', 'max:255'],
            'service_address.city' => ['required_if:preferred_service_method,pickup,home-service', 'string', 'max:120'],
            'service_address.region' => ['nullable', 'string', 'max:120'],
            'service_address.postal_code' => ['nullable', 'string', 'max:12'],
            'service_address.notes' => ['nullable', 'string', 'max:500'],
            'urgency' => ['nullable', Rule::in(['normal', 'urgent'])],
            'whatsapp_consent' => ['nullable', 'boolean'],
            'privacy_consent' => ['accepted'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'website' => ['nullable', 'size:0'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'privacy_consent.accepted' => 'Persetujuan privasi wajib diberikan.',
            'service_address.street.required_if' => 'Alamat wajib diisi untuk pickup atau home service.',
            'service_address.city.required_if' => 'Kota wajib diisi untuk pickup atau home service.',
            'attachments.*.max' => 'Ukuran setiap lampiran maksimal 5 MB.',
        ];
    }
}
