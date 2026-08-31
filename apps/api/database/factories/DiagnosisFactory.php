<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Diagnosis;
use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Diagnosis>
 */
final class DiagnosisFactory extends Factory
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
            'created_by' => null,
            'version' => 1,
            'repairability' => 'repairable',
            'summary' => fake()->sentence(12),
            'findings' => ['summary' => fake()->sentence()],
            'estimated_days' => 5,
            'internal_notes' => null,
            'diagnosed_at' => now(),
        ];
    }
}
