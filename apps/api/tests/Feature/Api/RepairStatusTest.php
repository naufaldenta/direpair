<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\ServiceRequest;
use App\Models\StatusEvent;
use App\RepairStatus;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

final class RepairStatusTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_high_entropy_token_returns_public_safe_repair_timeline(): void
    {
        $token = 'known-status-token-for-feature-test-1234567890';
        $serviceRequest = ServiceRequest::factory()->create([
            'public_token_hash' => hash_hmac('sha256', $token, (string) config('direpair.token_pepper')),
            'status' => RepairStatus::Diagnosing,
        ]);
        StatusEvent::factory()->for($serviceRequest)->create([
            'to_status' => RepairStatus::Diagnosing->value,
            'public_label' => RepairStatus::Diagnosing->label(),
        ]);

        $response = $this->getJson('/api/v1/status/'.$token);

        $response->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('Referrer-Policy', 'no-referrer')
            ->assertJsonPath('data.request_number', $serviceRequest->public_number)
            ->assertJsonPath('data.status', 'diagnosing')
            ->assertJsonMissingPath('data.customer')
            ->assertJsonMissingPath('data.symptom')
            ->assertJsonCount(1, 'data.timeline');
    }

    public function test_invalid_status_token_returns_not_found(): void
    {
        ServiceRequest::factory()->create();

        $this->getJson('/api/v1/status/'.str_repeat('x', 48))->assertNotFound();
    }
}
