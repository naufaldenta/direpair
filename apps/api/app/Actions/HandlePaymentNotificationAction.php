<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\PaymentGateway;
use App\InvoiceStatus;
use App\Models\AuditLog;
use App\Models\Payment;
use App\PaymentStatus;
use App\RepairStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class HandlePaymentNotificationAction
{
    public function __construct(
        private PaymentGateway $paymentGateway,
        private TransitionRepairStatusAction $transitionStatus,
    ) {}

    /** @param array<string, mixed> $payload */
    public function execute(array $payload): Payment
    {
        $verified = $this->paymentGateway->verifyNotification($payload);

        return DB::transaction(function () use ($verified): Payment {
            $payment = Payment::query()
                ->with('invoice.serviceRequest')
                ->where('external_order_id', $verified->externalOrderId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($payment->amount !== $verified->amount) {
                throw ValidationException::withMessages([
                    'gross_amount' => 'Payment amount does not match the invoice.',
                ]);
            }

            $previousStatus = $payment->status;

            if ($this->shouldIgnoreOutOfOrderStatus($previousStatus, $verified->status)) {
                $payment->update([
                    'last_notified_at' => now(),
                    'metadata' => [
                        'verified_at' => now()->toAtomString(),
                        'ignored_gateway_status' => $verified->gatewayStatus,
                    ],
                ]);

                AuditLog::query()->create([
                    'subject_type' => Payment::class,
                    'subject_id' => $payment->id,
                    'action' => 'payment.notification_ignored',
                    'before' => ['status' => $previousStatus->value],
                    'after' => [
                        'status' => $previousStatus->value,
                        'ignored_status' => $verified->status->value,
                        'gateway_status' => $verified->gatewayStatus,
                    ],
                ]);

                return $payment->refresh()->load('invoice.serviceRequest');
            }

            $payment->update([
                'gateway_transaction_id' => $verified->transactionId,
                'status' => $verified->status,
                'gateway_status' => $verified->gatewayStatus,
                'fraud_status' => $verified->fraudStatus,
                'paid_at' => $verified->status === PaymentStatus::Paid ? $verified->occurredAt : $payment->paid_at,
                'last_notified_at' => now(),
                'metadata' => ['verified_at' => now()->toAtomString()],
            ]);
            $invoiceStatus = match ($verified->status) {
                PaymentStatus::Paid => InvoiceStatus::Paid,
                PaymentStatus::Refunded => InvoiceStatus::Refunded,
                PaymentStatus::Cancelled => InvoiceStatus::Cancelled,
                PaymentStatus::Failed, PaymentStatus::Expired => InvoiceStatus::Failed,
                PaymentStatus::Pending, PaymentStatus::NotRequired => InvoiceStatus::Pending,
            };
            $invoice = $payment->invoice;
            $invoice->update([
                'status' => $invoiceStatus,
                'paid_amount' => $verified->status === PaymentStatus::Paid ? $verified->amount : $invoice->paid_amount,
                'paid_at' => $verified->status === PaymentStatus::Paid ? $verified->occurredAt : $invoice->paid_at,
            ]);
            $serviceRequest = $invoice->serviceRequest;
            $serviceRequest->update(['payment_status' => $verified->status]);

            if ($verified->status === PaymentStatus::Paid && $previousStatus !== PaymentStatus::Paid) {
                $target = match ($invoice->kind) {
                    'deposit' => RepairStatus::Repairing,
                    'final' => RepairStatus::ReadyForHandover,
                    default => null,
                };

                if ($target !== null) {
                    $this->transitionStatus->execute(
                        $serviceRequest,
                        $target,
                        publicMessage: $invoice->kind === 'deposit'
                            ? 'Deposit terverifikasi. Pekerjaan repair dimulai.'
                            : 'Pembayaran terverifikasi. Unit siap diserahkan.',
                    );
                }
            }

            AuditLog::query()->create([
                'subject_type' => Payment::class,
                'subject_id' => $payment->id,
                'action' => 'payment.notification_verified',
                'before' => ['status' => $previousStatus->value],
                'after' => [
                    'status' => $verified->status->value,
                    'gateway_status' => $verified->gatewayStatus,
                ],
            ]);

            return $payment->refresh()->load('invoice.serviceRequest');
        });
    }

    private function shouldIgnoreOutOfOrderStatus(PaymentStatus $current, PaymentStatus $incoming): bool
    {
        if ($current === PaymentStatus::Refunded) {
            return $incoming !== PaymentStatus::Refunded;
        }

        if ($current === PaymentStatus::Paid) {
            return ! in_array($incoming, [PaymentStatus::Paid, PaymentStatus::Refunded], true);
        }

        return false;
    }
}
