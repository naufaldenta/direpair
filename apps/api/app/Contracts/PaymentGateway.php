<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Payment;
use App\Payments\PaymentCheckout;
use App\Payments\VerifiedPaymentNotification;

interface PaymentGateway
{
    public function createCheckout(Payment $payment): PaymentCheckout;

    /** @param array<string, mixed> $payload */
    public function verifyNotification(array $payload): VerifiedPaymentNotification;
}
