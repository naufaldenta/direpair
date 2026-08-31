<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\CreateServiceRequestAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CreateServiceRequestRequest;
use App\Http\Resources\Api\V1\ServiceRequestCreatedResource;
use Illuminate\Http\JsonResponse;

final class ServiceRequestController extends Controller
{
    public function store(
        CreateServiceRequestRequest $request,
        CreateServiceRequestAction $action,
    ): JsonResponse {
        $result = $action->execute(
            $request->safe()->except(['attachments', 'privacy_consent', 'website']),
            $request->file('attachments', []),
            $request->ip(),
            $request->userAgent(),
        );

        return (new ServiceRequestCreatedResource($result))
            ->response()
            ->setStatusCode(201);
    }
}
