<?php

declare(strict_types=1);

namespace Database\Factories;

use App\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
final class InvoiceFactory extends Factory
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
            'quotation_id' => Quotation::factory(),
            'number' => 'INV-'.fake()->unique()->numerify('##########'),
            'kind' => 'deposit',
            'status' => InvoiceStatus::Pending,
            'currency' => 'IDR',
            'amount' => 100000,
            'paid_amount' => 0,
            'due_at' => now()->addDays(2),
        ];
    }
}
