<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\PaymentGateway;
use App\InvoiceStatus;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Quotation;
use App\PaymentStatus;
use App\QuotationStatus;
use App\RepairStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final readonly class ApproveQuotationAction
{
    public function __construct(
        private TransitionRepairStatusAction $transitionStatus,
        private PaymentGateway $paymentGateway,
    ) {}

    public function execute(
        Quotation $quotation,
        string $decision,
        ?string $ip,
        ?string $userAgent,
    ): Quotation {
        /** @var array{quotation: Quotation, payment: Payment|null} $result */
        $result = DB::transaction(function () use ($quotation, $decision, $ip, $userAgent): array {
            $locked = Quotation::query()
                ->with(['serviceRequest', 'invoices.payments'])
                ->lockForUpdate()
                ->findOrFail($quotation->id);

            if ($locked->status === QuotationStatus::Approved) {
                return [
                    'quotation' => $locked,
                    'payment' => $locked->invoices->flatMap->payments->first(),
                ];
            }

            if ($locked->status !== QuotationStatus::Sent) {
                throw ValidationException::withMessages(['quotation' => 'Quotation ini tidak lagi dapat diproses.']);
            }

            if ($locked->expires_at->isPast()) {
                throw ValidationException::withMessages(['quotation' => 'Quotation sudah kedaluwarsa.']);
            }

            if ($decision === 'reject') {
                $locked->update(['status' => QuotationStatus::Rejected]);
                $this->transitionStatus->execute(
                    $locked->serviceRequest,
                    RepairStatus::Cancelled,
                    publicMessage: 'Estimasi tidak disetujui. Tim Direpair akan menghubungi kamu mengenai pengembalian unit.',
                    ip: $ip,
                    userAgent: $userAgent,
                );
                $this->audit($locked, 'quotation.rejected', $ip, $userAgent);

                return ['quotation' => $locked, 'payment' => null];
            }

            $locked->update([
                'status' => QuotationStatus::Approved,
                'approved_at' => now(),
                'approval_ip_hash' => $this->hashIdentifier($ip),
            ]);
            $target = $locked->deposit_required
                ? RepairStatus::AwaitingDeposit
                : RepairStatus::Approved;
            $this->transitionStatus->execute(
                $locked->serviceRequest,
                $target,
                publicMessage: $locked->deposit_required
                    ? 'Estimasi disetujui. Repair dimulai setelah deposit terverifikasi.'
                    : 'Estimasi disetujui dan unit masuk antrean repair.',
                ip: $ip,
                userAgent: $userAgent,
            );
            $payment = null;

            if ($locked->deposit_required) {
                $invoice = Invoice::query()->create([
                    'uuid' => (string) Str::uuid(),
                    'service_request_id' => $locked->service_request_id,
                    'quotation_id' => $locked->id,
                    'number' => $this->newInvoiceNumber('DEP'),
                    'kind' => 'deposit',
                    'status' => InvoiceStatus::Pending,
                    'currency' => $locked->currency,
                    'amount' => $locked->deposit_amount,
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
                $locked->serviceRequest()->update(['payment_status' => PaymentStatus::Pending]);
            }

            $this->audit($locked, 'quotation.approved', $ip, $userAgent);

            return ['quotation' => $locked, 'payment' => $payment];
        });

        $payment = $result['payment'];

        if ($payment !== null && ($payment->checkout_token === null || $payment->checkout_url === null)) {
            $checkout = $this->paymentGateway->createCheckout($payment);
            $payment->update([
                'checkout_token' => $checkout->token,
                'checkout_url' => $checkout->url,
            ]);
        }

        return $result['quotation']->refresh()->load(['diagnosis', 'items', 'invoices.payments']);
    }

    private function newInvoiceNumber(string $prefix): string
    {
        do {
            $number = sprintf('%s-%s-%s', $prefix, now()->format('ymd'), Str::upper(Str::random(6)));
        } while (Invoice::query()->where('number', $number)->exists());

        return $number;
    }

    private function audit(Quotation $quotation, string $action, ?string $ip, ?string $userAgent): void
    {
        AuditLog::query()->create([
            'subject_type' => Quotation::class,
            'subject_id' => $quotation->id,
            'action' => $action,
            'after' => ['status' => $quotation->status->value],
            'ip_hash' => $this->hashIdentifier($ip),
            'user_agent_hash' => $this->hashIdentifier($userAgent),
        ]);
    }

    private function hashIdentifier(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return hash_hmac('sha256', $value, (string) config('direpair.token_pepper'));
    }
}
