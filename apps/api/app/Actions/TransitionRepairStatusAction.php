<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\ServiceRequest;
use App\RepairStatus;
use App\Services\RepairWorkflow;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class TransitionRepairStatusAction
{
    public function __construct(private RepairWorkflow $workflow) {}

    public function execute(
        ServiceRequest $serviceRequest,
        RepairStatus $target,
        ?int $actorUserId = null,
        ?string $publicMessage = null,
        bool $visibleToCustomer = true,
        ?string $ip = null,
        ?string $userAgent = null,
    ): ServiceRequest {
        return DB::transaction(function () use (
            $serviceRequest,
            $target,
            $actorUserId,
            $publicMessage,
            $visibleToCustomer,
            $ip,
            $userAgent,
        ): ServiceRequest {
            $locked = ServiceRequest::query()->lockForUpdate()->findOrFail($serviceRequest->id);
            $current = $locked->status;

            if (! $current instanceof RepairStatus || ! $this->workflow->canTransition($current, $target)) {
                throw ValidationException::withMessages([
                    'status' => sprintf(
                        'Status tidak dapat diubah dari %s ke %s.',
                        $current instanceof RepairStatus ? $current->value : (string) $current,
                        $target->value,
                    ),
                ]);
            }

            $locked->update(['status' => $target]);
            $locked->statusEvents()->create([
                'actor_user_id' => $actorUserId,
                'from_status' => $current->value,
                'to_status' => $target->value,
                'public_label' => $target->label(),
                'public_message' => $publicMessage,
                'visible_to_customer' => $visibleToCustomer,
                'occurred_at' => now(),
            ]);
            AuditLog::query()->create([
                'actor_user_id' => $actorUserId,
                'subject_type' => ServiceRequest::class,
                'subject_id' => $locked->id,
                'action' => 'repair.status_changed',
                'before' => ['status' => $current->value],
                'after' => ['status' => $target->value],
                'ip_hash' => $this->hashIdentifier($ip),
                'user_agent_hash' => $this->hashIdentifier($userAgent),
            ]);

            return $locked->refresh();
        });
    }

    private function hashIdentifier(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return hash_hmac('sha256', $value, (string) config('direpair.token_pepper'));
    }
}
