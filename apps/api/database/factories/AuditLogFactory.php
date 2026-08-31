<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
final class AuditLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'actor_user_id' => null,
            'subject_type' => ServiceRequest::class,
            'subject_id' => ServiceRequest::factory(),
            'action' => 'repair.test_event',
            'before' => null,
            'after' => ['status' => 'submitted'],
            'ip_hash' => null,
            'user_agent_hash' => null,
        ];
    }
}
