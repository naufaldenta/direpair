<?php

declare(strict_types=1);

namespace App\Payments;

use App\PaymentStatus;
use Carbon\CarbonImmutable;

final readonly class VerifiedPaymentNotification
{
    public function __construct(
        public string $externalOrderId,
        public ?string $transactionId,
        public PaymentStatus $status,
        public string $gatewayStatus,
        public ?string $fraudStatus,
        public int $amount,
        public CarbonImmutable $occurredAt,
    ) {}
}
