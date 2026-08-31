<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\PaymentGateway;
use App\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\ServiceRequest;
use App\PaymentStatus;
use App\QuotationStatus;
use App\RepairStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final readonly class CreateFinalInvoiceAction
{
    public function __construct(
        private TransitionRepairStatusAction $transitionStatus,
        private PaymentGateway $paymentGateway,
    ) {}

    public function execute(ServiceRequest $serviceRequest, int $actorUserId, ?string $message = null): ?Payment
    {
        /** @var array{payment: Payment|null, needs_checkout: bool} $result */
        $result = DB::transaction(function () use ($serviceRequest, $actorUserId, $message): array {
            $locked = ServiceRequest::query()->lockForUpdate()->findOrFail($serviceRequest->id);
            $quotation = $locked->quotations()
                ->where('status', QuotationStatus::Approved->value)
                ->latest('version')
                ->first();

            if ($quotation === null) {
                throw ValidationException::withMessages(['status' => 'Tidak ada quotation approved untuk dibuatkan invoice final.']);
            }

            $existing = $quotation->invoices()->where('kind', 'final')->with('payments')->first();

            if ($existing !== null) {
                return ['payment' => $existing->payments->first(), 'needs_checkout' => true];
            }

            $paidAmount = (int) $quotation->invoices()->sum('paid_amount');
            $remaining = max(0, $quotation->total - $paidAmount);

            if ($remaining === 0) {
                $this->transitionStatus->execute(
                    $locked,
                    RepairStatus::ReadyForHandover,
                    $actorUserId,
                    $message ?: 'Quality control selesai. Unit siap diserahkan.',
                );

                return ['payment' => null, 'needs_checkout' => false];
            }

            $invoice = Invoice::query()->create([
                'uuid' => (string) Str::uuid(),
                'service_request_id' => $locked->id,
                'quotation_id' => $quotation->id,
                'number' => $this->newInvoiceNumber(),
                'kind' => 'final',
                'status' => InvoiceStatus::Pending,
                'currency' => $quotation->currency,
                'amount' => $remaining,
                'paid_amount' => 0,
                'due_at' => now()->addDays(2),
            ]);
            $payment = Payment::query()->create([
                'uuid' => (string) Str::uuid(),
                'invoice_id' => $invoice->id,
                'gateway' => (string) config('direpair.payment_driver'),
                'external_order_id' => $invoice->number,
                'status' => PaymentStatus::Pending,
                'currency' => $invoice->currency,
                'amount' => $invoice->amount,
            ]);
            $locked->update(['payment_status' => PaymentStatus::Pending]);
            $this->transitionStatus->execute(
                $locked,
                RepairStatus::AwaitingFinalPayment,
                $actorUserId,
                $message ?: 'Repair dan quality control selesai. Menunggu pelunasan sebelum serah terima.',
            );

            return ['payment' => $payment, 'needs_checkout' => true];
        });
        $payment = $result['payment'];

        if ($payment !== null && $result['needs_checkout'] && ($payment->checkout_url === null || $payment->checkout_token === null)) {
            $checkout = $this->paymentGateway->createCheckout($payment);
            $payment->update(['checkout_token' => $checkout->token, 'checkout_url' => $checkout->url]);
        }

        return $payment?->refresh();
    }

    private function newInvoiceNumber(): string
    {
        do {
            $number = 'FIN-'.now()->format('ymd').'-'.Str::upper(Str::random(6));
        } while (Invoice::query()->where('number', $number)->exists());

        return $number;
    }
}
