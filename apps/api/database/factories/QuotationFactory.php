<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Quotation;
use App\Models\ServiceRequest;
use App\QuotationStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quotation>
 */
final class QuotationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => fake()->uuid(),
            'service_request_id' => ServiceRequest::factory(),
            'diagnosis_id' => null,
            'created_by' => null,
            'version' => 1,
            'status' => QuotationStatus::Sent,
            'currency' => 'IDR',
            'subtotal' => 350000,
            'discount' => 0,
            'tax' => 0,
            'total' => 350000,
            'deposit_required' => false,
            'deposit_amount' => 0,
            'customer_notes' => null,
            'sent_at' => now(),
            'expires_at' => now()->addDays(7),
        ];
    }
}
