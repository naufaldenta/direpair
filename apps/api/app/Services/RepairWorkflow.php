<?php

declare(strict_types=1);

namespace App\Services;

use App\RepairStatus;

final class RepairWorkflow
{
    /** @return list<RepairStatus> */
    public function allowedNext(RepairStatus $current): array
    {
        return match ($current) {
            RepairStatus::Submitted => [RepairStatus::AwaitingUnit, RepairStatus::UnitReceived, RepairStatus::Cancelled],
            RepairStatus::AwaitingUnit => [RepairStatus::UnitReceived, RepairStatus::Cancelled],
            RepairStatus::UnitReceived => [RepairStatus::Diagnosing, RepairStatus::Cancelled],
            RepairStatus::Diagnosing => [RepairStatus::AwaitingApproval, RepairStatus::Cancelled],
            RepairStatus::AwaitingApproval => [
                RepairStatus::Approved,
                RepairStatus::AwaitingDeposit,
                RepairStatus::Cancelled,
            ],
            RepairStatus::Approved => [RepairStatus::Repairing, RepairStatus::Cancelled],
            RepairStatus::AwaitingDeposit => [RepairStatus::Approved, RepairStatus::Repairing, RepairStatus::Cancelled],
            RepairStatus::Repairing => [RepairStatus::QualityControl, RepairStatus::Cancelled],
            RepairStatus::QualityControl => [
                RepairStatus::Repairing,
                RepairStatus::AwaitingFinalPayment,
                RepairStatus::ReadyForHandover,
            ],
            RepairStatus::AwaitingFinalPayment => [RepairStatus::ReadyForHandover],
            RepairStatus::ReadyForHandover => [RepairStatus::Completed, RepairStatus::WarrantyReview],
            RepairStatus::Completed => [RepairStatus::WarrantyReview],
            RepairStatus::WarrantyReview => [RepairStatus::Diagnosing, RepairStatus::Completed, RepairStatus::Cancelled],
            RepairStatus::Cancelled => [],
        };
    }

    public function canTransition(RepairStatus $current, RepairStatus $target): bool
    {
        return in_array($target, $this->allowedNext($current), true);
    }
}
