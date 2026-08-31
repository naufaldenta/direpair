<?php

declare(strict_types=1);

namespace App\Payments;

use App\Contracts\PaymentGateway;
use App\Models\Payment;
use App\PaymentStatus;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

final class MockPaymentGateway implements PaymentGateway
{
    public function createCheckout(Payment $payment): PaymentCheckout
    {
        $frontendUrl = rtrim((string) config('direpair.frontend_url'), '/');

        return new PaymentCheckout(
            token: 'mock_'.$payment->uuid,
            url: $frontendUrl.'/mock-payment?order_id='.urlencode($payment->external_order_id).'&amount='.$payment->amount,
        );
    }

    public function verifyNotification(array $payload): VerifiedPaymentNotification
    {
        if (! app()->environment(['local', 'testing'])) {
            throw ValidationException::withMessages([
                'transaction_status' => 'Mock payment notifications are disabled in this environment.',
            ]);
        }

        if (($payload['transaction_status'] ?? null) !== 'mock-paid') {
            throw ValidationException::withMessages([
                'transaction_status' => 'Unsupported mock transaction status.',
            ]);
        }

        return new VerifiedPaymentNotification(
            externalOrderId: (string) $payload['order_id'],
            transactionId: isset($payload['transaction_id']) ? (string) $payload['transaction_id'] : null,
            status: PaymentStatus::Paid,
            gatewayStatus: 'mock-paid',
            fraudStatus: null,
            amount: (int) round((float) $payload['gross_amount']),
            occurredAt: CarbonImmutable::now(),
        );
    }
}
