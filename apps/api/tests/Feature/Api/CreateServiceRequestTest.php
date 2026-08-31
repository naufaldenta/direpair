<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\ServiceRequest;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

final class CreateServiceRequestTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_customer_can_create_a_repair_request_and_receives_one_time_status_token(): void
    {
        $response = $this->postJson('/api/v1/service-requests', $this->validPayload());

        $response->assertCreated()
            ->assertJsonPath('data.status', 'submitted')
            ->assertJsonStructure(['data' => ['request_number', 'status_token', 'status_url', 'next_step']]);
        $statusToken = (string) $response->json('data.status_token');
        $serviceRequest = ServiceRequest::query()->sole();

        self::assertGreaterThanOrEqual(48, strlen($statusToken));
        self::assertNotSame($statusToken, $serviceRequest->public_token_hash);
        self::assertSame(
            hash_hmac('sha256', $statusToken, (string) config('direpair.token_pepper')),
            $serviceRequest->public_token_hash,
        );
        $this->assertDatabaseHas('status_events', [
            'service_request_id' => $serviceRequest->id,
            'to_status' => 'submitted',
        ]);
        $this->assertDatabaseHas('customers', [
            'id' => $serviceRequest->customer_id,
            'privacy_consent_version' => 'draft-local',
        ]);
    }

    public function test_booking_requires_privacy_consent_and_rejects_honeypot_submission(): void
    {
        $payload = $this->validPayload();
        $payload['privacy_consent'] = false;
        $payload['website'] = 'spam.example';

        $this->postJson('/api/v1/service-requests', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['privacy_consent', 'website']);
        $this->assertDatabaseCount('service_requests', 0);
    }

    /** @return array<string, mixed> */
    private function validPayload(): array
    {
        return [
            'name' => 'Raka Test',
            'phone' => '0812 3456 7890',
            'email' => 'raka@example.test',
            'service_slug' => 'tws',
            'device_category' => 'TWS',
            'brand' => 'Test Brand',
            'model' => 'Model X',
            'symptom' => 'Earbud kanan tidak dapat mengisi dan mati setelah beberapa menit.',
            'preferred_service_method' => 'drop-off',
            'urgency' => 'normal',
            'whatsapp_consent' => true,
            'privacy_consent' => true,
            'website' => '',
        ];
    }
}
