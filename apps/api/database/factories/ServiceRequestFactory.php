<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Customer;
use App\Models\ServiceRequest;
use App\PaymentStatus;
use App\RepairStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceRequest>
 */
final class ServiceRequestFactory extends Factory
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
            'customer_id' => Customer::factory(),
            'public_number' => 'DRP-'.fake()->unique()->numerify('##########'),
            'public_token_hash' => hash('sha256', fake()->unique()->uuid()),
            'service_slug' => fake()->randomElement(['tws', 'vacuum', 'sepeda-listrik']),
            'device_category' => fake()->randomElement(['TWS', 'Vacuum', 'Sepeda listrik']),
            'brand' => fake()->company(),
            'model' => fake()->bothify('Model-###??'),
            'serial_number' => null,
            'symptom' => fake()->sentence(12),
            'preferred_service_method' => 'drop-off',
            'service_address' => null,
            'urgency' => 'normal',
            'status' => RepairStatus::Submitted,
            'payment_status' => PaymentStatus::NotRequired,
            'source' => 'website',
            'is_demo' => false,
            'submitted_at' => now(),
        ];
    }
}
