<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\PaymentGateway;
use App\Payments\MidtransSnapGateway;
use App\Payments\MockPaymentGateway;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentGateway::class, function (): PaymentGateway {
            return match ((string) config('direpair.payment_driver')) {
                'midtrans' => new MidtransSnapGateway,
                'mock' => new MockPaymentGateway,
                default => throw new \RuntimeException('Unsupported payment driver.'),
            };
        });
    }

    public function boot(): void
    {
        RateLimiter::for('booking', fn (Request $request): Limit => Limit::perMinute(12)->by($request->ip()));
        RateLimiter::for('payment-webhook', fn (Request $request): Limit => Limit::perMinute(120)->by($request->ip()));
    }
}
