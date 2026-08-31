<?php

declare(strict_types=1);

namespace App\Payments;

use App\Contracts\PaymentGateway;
use App\Models\Payment;
use App\PaymentStatus;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

final class MidtransSnapGateway implements PaymentGateway
{
    public function createCheckout(Payment $payment): PaymentCheckout
    {
        $payment->loadMissing('invoice.serviceRequest.customer');
        $serverKey = (string) config('services.midtrans.server_key');

        if ($serverKey === '') {
            throw new \RuntimeException('MIDTRANS_SERVER_KEY is not configured.');
        }

        $customer = $payment->invoice->serviceRequest->customer;
        $response = Http::withBasicAuth($serverKey, '')
            ->acceptJson()
            ->timeout(10)
            ->retry(2, 250)
            ->post(rtrim((string) config('services.midtrans.snap_base_url'), '/').'/snap/v1/transactions', [
                'transaction_details' => [
                    'order_id' => $payment->external_order_id,
                    'gross_amount' => $payment->amount,
                ],
                'item_details' => [[
                    'id' => $payment->invoice->number,
                    'price' => $payment->amount,
                    'quantity' => 1,
                    'name' => 'Direpair '.$payment->invoice->kind.' payment',
                ]],
                'customer_details' => [
                    'first_name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                ],
                'callbacks' => [
                    'finish' => (string) config('direpair.public_status_url'),
                ],
            ])
            ->throw();
        $data = $response->json();

        if (! is_array($data) || ! is_string($data['token'] ?? null) || ! is_string($data['redirect_url'] ?? null)) {
            throw new \RuntimeException('Midtrans returned an invalid checkout response.');
        }

        return new PaymentCheckout($data['token'], $data['redirect_url']);
    }

    public function verifyNotification(array $payload): VerifiedPaymentNotification
    {
        $serverKey = (string) config('services.midtrans.server_key');
        $signature = (string) ($payload['signature_key'] ?? '');
        $expected = hash(
            'sha512',
            (string) $payload['order_id'].
            (string) $payload['status_code'].
            (string) $payload['gross_amount'].
            $serverKey,
        );

        if ($serverKey === '' || ! hash_equals($expected, $signature)) {
            throw ValidationException::withMessages(['signature_key' => 'Invalid Midtrans signature.']);
        }

        $gatewayStatus = (string) $payload['transaction_status'];
        $fraudStatus = isset($payload['fraud_status']) ? (string) $payload['fraud_status'] : null;
        $status = match ($gatewayStatus) {
            'settlement' => PaymentStatus::Paid,
            'capture' => $fraudStatus === 'challenge' ? PaymentStatus::Pending : PaymentStatus::Paid,
            'pending' => PaymentStatus::Pending,
            'expire' => PaymentStatus::Expired,
            'cancel' => PaymentStatus::Cancelled,
            'refund', 'partial_refund' => PaymentStatus::Refunded,
            default => PaymentStatus::Failed,
        };
        $occurredAt = $payload['settlement_time'] ?? $payload['transaction_time'] ?? null;

        return new VerifiedPaymentNotification(
            externalOrderId: (string) $payload['order_id'],
            transactionId: isset($payload['transaction_id']) ? (string) $payload['transaction_id'] : null,
            status: $status,
            gatewayStatus: $gatewayStatus,
            fraudStatus: $fraudStatus,
            amount: (int) round((float) $payload['gross_amount']),
            occurredAt: $occurredAt === null ? CarbonImmutable::now() : CarbonImmutable::parse((string) $occurredAt),
        );
    }
}
