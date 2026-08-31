<?php

declare(strict_types=1);

namespace App\Http\Requests\Operations;

use App\RepairStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class TransitionStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(RepairStatus::class)],
            'public_message' => ['nullable', 'string', 'max:1000'],
            'visible_to_customer' => ['nullable', 'boolean'],
        ];
    }
}
