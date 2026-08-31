<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Quotation;
use App\Models\ServiceRequest;
use App\PaymentStatus;
use App\QuotationStatus;
use App\RepairStatus;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

final class MidtransWebhookTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_verified_mock_notification_is_idempotent_and_advances_repair(): void
    {
        config()->set('direpair.payment_driver', 'mock');
        $serviceRequest = ServiceRequest::factory()->create([
            'status' => RepairStatus::AwaitingDeposit,
            'payment_status' => PaymentStatus::Pending,
        ]);
        $quotation = Quotation::factory()->for($serviceRequest)->create(['status' => QuotationStatus::Approved]);
        $invoice = Invoice::factory()->for($serviceRequest)->for($quotation)->create([
            'status' => InvoiceStatus::Pending,
            'amount' => 150000,
        ]);
        $payment = Payment::factory()->for($invoice)->create([
            'external_order_id' => $invoice->number,
            'amount' => 150000,
        ]);
        $payload = [
            'order_id' => $payment->external_order_id,
            'status_code' => '200',
            'gross_amount' => '150000.00',
            'transaction_status' => 'mock-paid',
            'transaction_id' => 'mock-transaction-1',
        ];

        $this->postJson('/api/v1/payments/midtrans/webhook', $payload)
            ->assertOk()
            ->assertJsonPath('status', 'paid');
        $this->postJson('/api/v1/payments/midtrans/webhook', $payload)->assertOk();

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'paid']);
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'paid', 'paid_amount' => 150000]);
        $this->assertDatabaseHas('service_requests', ['id' => $serviceRequest->id, 'status' => 'repairing']);
        self::assertSame(1, $serviceRequest->statusEvents()->where('to_status', 'repairing')->count());
    }

    public function test_late_pending_notification_cannot_regress_a_settled_payment(): void
    {
        config()->set('direpair.payment_driver', 'midtrans');
        config()->set('services.midtrans.server_key', 'midtrans-test-server-key');
        $serviceRequest = ServiceRequest::factory()->create([
            'status' => RepairStatus::AwaitingDeposit,
            'payment_status' => PaymentStatus::Pending,
        ]);
        $quotation = Quotation::factory()->for($serviceRequest)->create(['status' => QuotationStatus::Approved]);
        $invoice = Invoice::factory()->for($serviceRequest)->for($quotation)->create([
            'kind' => 'deposit',
            'status' => InvoiceStatus::Pending,
            'amount' => 150000,
        ]);
        $payment = Payment::factory()->for($invoice)->create([
            'external_order_id' => $invoice->number,
            'amount' => 150000,
            'status' => PaymentStatus::Pending,
        ]);

        $settlement = $this->signedMidtransPayload($payment, 'settlement', '200');
        $pending = $this->signedMidtransPayload($payment, 'pending', '201');

        $this->postJson('/api/v1/payments/midtrans/webhook', $settlement)
            ->assertOk()
            ->assertJsonPath('status', 'paid');
        $this->postJson('/api/v1/payments/midtrans/webhook', $pending)
            ->assertOk()
            ->assertJsonPath('status', 'paid');

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'paid']);
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'paid', 'paid_amount' => 150000]);
        $this->assertDatabaseHas('service_requests', [
            'id' => $serviceRequest->id,
            'status' => 'repairing',
            'payment_status' => 'paid',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'subject_type' => Payment::class,
            'subject_id' => $payment->id,
            'action' => 'payment.notification_ignored',
        ]);
        self::assertSame(1, $serviceRequest->statusEvents()->where('to_status', 'repairing')->count());
    }

    /** @return array<string, string> */
    private function signedMidtransPayload(Payment $payment, string $transactionStatus, string $statusCode): array
    {
        $payload = [
            'order_id' => $payment->external_order_id,
            'status_code' => $statusCode,
            'gross_amount' => '150000.00',
            'transaction_status' => $transactionStatus,
            'transaction_id' => 'midtrans-'.$transactionStatus,
            'transaction_time' => '2026-08-31 12:00:00',
        ];
        $payload['signature_key'] = hash(
            'sha512',
            $payload['order_id'].$payload['status_code'].$payload['gross_amount'].'midtrans-test-server-key',
        );

        return $payload;
    }
}
