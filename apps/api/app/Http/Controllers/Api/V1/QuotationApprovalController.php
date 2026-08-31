<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\ApproveQuotationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ApproveQuotationRequest;
use App\Http\Resources\Api\V1\QuotationResource;
use App\Models\ServiceRequest;

final class QuotationApprovalController extends Controller
{
    public function __invoke(
        ApproveQuotationRequest $request,
        string $token,
        string $quotationUuid,
        ApproveQuotationAction $action,
    ): QuotationResource {
        abort_if(strlen($token) < 32, 404);
        $hash = hash_hmac('sha256', $token, (string) config('direpair.token_pepper'));
        $serviceRequest = ServiceRequest::query()->where('public_token_hash', $hash)->firstOrFail();
        $quotation = $serviceRequest->quotations()->where('uuid', $quotationUuid)->firstOrFail();
        $quotation = $action->execute(
            $quotation,
            (string) $request->validated('action'),
            $request->ip(),
            $request->userAgent(),
        );

        return new QuotationResource($quotation);
    }
}
