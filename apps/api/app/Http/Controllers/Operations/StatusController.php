<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operations;

use App\Actions\CreateFinalInvoiceAction;
use App\Actions\TransitionRepairStatusAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\TransitionStatusRequest;
use App\Models\ServiceRequest;
use App\RepairStatus;
use Illuminate\Http\RedirectResponse;

final class StatusController extends Controller
{
    public function update(
        TransitionStatusRequest $request,
        ServiceRequest $serviceRequest,
        TransitionRepairStatusAction $transition,
        CreateFinalInvoiceAction $createFinalInvoice,
    ): RedirectResponse {
        $target = RepairStatus::from((string) $request->validated('status'));
        $message = $request->validated('public_message');

        if ($target === RepairStatus::AwaitingFinalPayment) {
            $createFinalInvoice->execute($serviceRequest, (int) $request->user()->id, $message);
        } else {
            $transition->execute(
                $serviceRequest,
                $target,
                (int) $request->user()->id,
                $message,
                $request->boolean('visible_to_customer', true),
                $request->ip(),
                $request->userAgent(),
            );
        }

        return redirect()
            ->route('operations.requests.show', $serviceRequest)
            ->with('success', 'Status repair berhasil diperbarui.');
    }
}
