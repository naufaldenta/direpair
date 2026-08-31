<?php

declare(strict_types=1);

namespace App\Payments;

final readonly class PaymentCheckout
{
    public function __construct(
        public string $token,
        public string $url,
    ) {}
}
