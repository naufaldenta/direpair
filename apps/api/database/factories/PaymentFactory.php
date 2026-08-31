<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Payment;
use App\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
final class PaymentFactory extends Factory
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
            'invoice_id' => Invoice::factory(),
            'gateway' => 'mock',
            'external_order_id' => 'PAY-'.fake()->unique()->numerify('##########'),
            'gateway_transaction_id' => null,
            'status' => PaymentStatus::Pending,
            'currency' => 'IDR',
            'amount' => 100000,
            'gateway_status' => null,
            'fraud_status' => null,
            'checkout_token' => null,
            'checkout_url' => null,
            'metadata' => null,
        ];
    }
}
