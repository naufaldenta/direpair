<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\HandlePaymentNotificationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\MidtransNotificationRequest;
use Illuminate\Http\JsonResponse;

final class MidtransWebhookController extends Controller
{
    public function __invoke(
        MidtransNotificationRequest $request,
        HandlePaymentNotificationAction $action,
    ): JsonResponse {
        $payment = $action->execute($request->validated());

        return response()->json([
            'accepted' => true,
            'order_id' => $payment->external_order_id,
            'status' => $payment->status->value,
        ]);
    }
}
