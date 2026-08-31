<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\MidtransWebhookController;
use App\Http\Controllers\Api\V1\QuotationApprovalController;
use App\Http\Controllers\Api\V1\RepairStatusController;
use App\Http\Controllers\Api\V1\ServiceRequestController;
use App\Http\Middleware\NoStorePrivateResponse;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', fn (): array => ['status' => 'ok', 'service' => 'direpair-operations']);

    Route::middleware(['throttle:booking', NoStorePrivateResponse::class])->group(function (): void {
        Route::post('/service-requests', [ServiceRequestController::class, 'store']);
        Route::get('/status/{token}', RepairStatusController::class);
        Route::post('/status/{token}/quotations/{quotationUuid}/decision', QuotationApprovalController::class);
    });

    Route::post('/payments/midtrans/webhook', MidtransWebhookController::class)
        ->middleware('throttle:payment-webhook');
});
