<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
final class CustomerFactory extends Factory
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
            'name' => fake()->name(),
            'phone' => '+628'.fake()->numerify('##########'),
            'email' => fake()->unique()->safeEmail(),
            'whatsapp_consent' => true,
            'privacy_consent_at' => now(),
            'privacy_consent_version' => 'factory-v1',
        ];
    }
}
