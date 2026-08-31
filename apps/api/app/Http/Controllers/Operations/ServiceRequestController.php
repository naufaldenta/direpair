<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\RepairStatus;
use App\Services\RepairWorkflow;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ServiceRequestController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $requests = ServiceRequest::query()
            ->with('customer')
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest('submitted_at')
            ->paginate(20)
            ->withQueryString();

        return view('operations.index', [
            'requests' => $requests,
            'statuses' => RepairStatus::cases(),
            'selectedStatus' => $status,
        ]);
    }

    public function show(ServiceRequest $serviceRequest, RepairWorkflow $workflow): View
    {
        $serviceRequest->load([
            'customer',
            'media',
            'diagnoses.creator',
            'quotations' => fn ($query) => $query->with(['items', 'diagnosis', 'invoices.payments'])->latest('version'),
            'statusEvents.actor',
        ]);

        return view('operations.show', [
            'serviceRequest' => $serviceRequest,
            'allowedStatuses' => $workflow->allowedNext($serviceRequest->status),
        ]);
    }
}
