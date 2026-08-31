<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operations;

use App\Actions\CreateQuotationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\StoreQuotationRequest;
use App\Models\ServiceRequest;
use Illuminate\Http\RedirectResponse;

final class QuotationController extends Controller
{
    public function store(
        StoreQuotationRequest $request,
        ServiceRequest $serviceRequest,
        CreateQuotationAction $action,
    ): RedirectResponse {
        $action->execute($serviceRequest, $request->validated(), (int) $request->user()->id);

        return redirect()
            ->route('operations.requests.show', $serviceRequest)
            ->with('success', 'Diagnosis dan quotation baru sudah dikirim ke halaman status pelanggan.');
    }
}
