<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\ServiceRequest;
use App\QuotationStatus;
use App\RepairStatus;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

final class QuotationApprovalTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_customer_can_approve_current_quote_and_receive_deposit_checkout(): void
    {
        config()->set('direpair.payment_driver', 'mock');
        $token = 'quotation-status-token-feature-test-123456789';
        $serviceRequest = ServiceRequest::factory()->create([
            'public_token_hash' => hash_hmac('sha256', $token, (string) config('direpair.token_pepper')),
            'status' => RepairStatus::AwaitingApproval,
        ]);
        $quotation = Quotation::factory()->for($serviceRequest)->create([
            'status' => QuotationStatus::Sent,
            'total' => 400000,
            'deposit_required' => true,
            'deposit_amount' => 150000,
        ]);
        QuotationItem::factory()->for($quotation)->create(['total' => 400000, 'unit_price' => 400000]);

        $response = $this->postJson(
            sprintf('/api/v1/status/%s/quotations/%s/decision', $token, $quotation->uuid),
            ['action' => 'approve', 'terms_accepted' => true, 'website' => ''],
        );

        $response->assertOk()
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.invoice.kind', 'deposit')
            ->assertJsonPath('data.invoice.amount', 150000);
        self::assertStringContainsString('mock-payment', (string) $response->json('data.invoice.checkout_url'));
        self::assertStringContainsString('amount=150000', (string) $response->json('data.invoice.checkout_url'));
        $this->assertDatabaseHas('service_requests', ['id' => $serviceRequest->id, 'status' => 'awaiting_deposit']);
        $this->assertDatabaseHas('payments', ['amount' => 150000, 'status' => 'pending']);
    }
}
