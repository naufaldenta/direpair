<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\Diagnosis;
use App\Models\Quotation;
use App\Models\ServiceRequest;
use App\QuotationStatus;
use App\RepairStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final readonly class CreateQuotationAction
{
    public function __construct(private TransitionRepairStatusAction $transitionStatus) {}

    /** @param array<string, mixed> $data */
    public function execute(ServiceRequest $serviceRequest, array $data, int $actorUserId): Quotation
    {
        return DB::transaction(function () use ($serviceRequest, $data, $actorUserId): Quotation {
            $locked = ServiceRequest::query()->lockForUpdate()->findOrFail($serviceRequest->id);

            if ($locked->status === RepairStatus::UnitReceived) {
                $locked = $this->transitionStatus->execute(
                    $locked,
                    RepairStatus::Diagnosing,
                    $actorUserId,
                    'Unit sedang diperiksa oleh teknisi.',
                );
            }

            if (! in_array($locked->status, [RepairStatus::Diagnosing, RepairStatus::AwaitingApproval], true)) {
                throw ValidationException::withMessages([
                    'service_request' => 'Quotation hanya dapat dibuat saat unit sedang didiagnosis atau menunggu approval.',
                ]);
            }

            $diagnosisVersion = ((int) $locked->diagnoses()->max('version')) + 1;
            $diagnosis = Diagnosis::query()->create([
                'service_request_id' => $locked->id,
                'created_by' => $actorUserId,
                'version' => $diagnosisVersion,
                'repairability' => (string) $data['repairability'],
                'summary' => (string) $data['diagnosis_summary'],
                'findings' => empty($data['findings']) ? null : ['summary' => (string) $data['findings']],
                'estimated_days' => $data['estimated_days'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
                'diagnosed_at' => now(),
            ]);
            $locked->quotations()
                ->where('status', QuotationStatus::Sent->value)
                ->update(['status' => QuotationStatus::Superseded->value]);
            $version = ((int) $locked->quotations()->max('version')) + 1;
            $items = collect($data['items'])->values()->map(function (array $item, int $index): array {
                $quantity = (float) $item['quantity'];
                $unitPrice = (int) $item['unit_price'];

                return [
                    'type' => (string) $item['type'],
                    'label' => (string) $item['label'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total' => (int) round($quantity * $unitPrice),
                    'sort_order' => $index,
                ];
            });
            $subtotal = (int) $items->sum('total');
            $discount = (int) ($data['discount'] ?? 0);
            $tax = (int) ($data['tax'] ?? 0);
            $total = $subtotal - $discount + $tax;
            $depositRequired = (bool) ($data['deposit_required'] ?? false);
            $depositAmount = $depositRequired ? (int) ($data['deposit_amount'] ?? 0) : 0;

            if ($total < 0) {
                throw ValidationException::withMessages(['discount' => 'Discount tidak boleh melebihi subtotal dan pajak.']);
            }

            if ($depositRequired && ($depositAmount <= 0 || $depositAmount > $total)) {
                throw ValidationException::withMessages([
                    'deposit_amount' => 'Deposit wajib lebih dari nol dan tidak boleh melebihi total quotation.',
                ]);
            }

            $quotation = Quotation::query()->create([
                'uuid' => (string) Str::uuid(),
                'service_request_id' => $locked->id,
                'diagnosis_id' => $diagnosis->id,
                'created_by' => $actorUserId,
                'version' => $version,
                'status' => QuotationStatus::Sent,
                'currency' => 'IDR',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'deposit_required' => $depositRequired,
                'deposit_amount' => $depositAmount,
                'customer_notes' => $data['customer_notes'] ?? null,
                'sent_at' => now(),
                'expires_at' => now()->addDays((int) ($data['valid_days'] ?? 7)),
            ]);
            $quotation->items()->createMany($items->all());

            if ($locked->status === RepairStatus::Diagnosing) {
                $this->transitionStatus->execute(
                    $locked,
                    RepairStatus::AwaitingApproval,
                    $actorUserId,
                    'Diagnosis dan estimasi biaya sudah tersedia untuk ditinjau.',
                );
            }

            AuditLog::query()->create([
                'actor_user_id' => $actorUserId,
                'subject_type' => Quotation::class,
                'subject_id' => $quotation->id,
                'action' => 'quotation.sent',
                'after' => [
                    'version' => $version,
                    'total' => $total,
                    'deposit_required' => $depositRequired,
                    'deposit_amount' => $depositAmount,
                ],
            ]);

            return $quotation->load(['diagnosis', 'items', 'invoices.payments']);
        });
    }
}
