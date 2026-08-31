<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ServiceRequest;
use App\Models\StatusEvent;
use App\RepairStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StatusEvent>
 */
final class StatusEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_request_id' => ServiceRequest::factory(),
            'actor_user_id' => null,
            'from_status' => null,
            'to_status' => RepairStatus::Submitted->value,
            'public_label' => RepairStatus::Submitted->label(),
            'public_message' => fake()->sentence(),
            'visible_to_customer' => true,
            'metadata' => null,
            'occurred_at' => now(),
        ];
    }
}
