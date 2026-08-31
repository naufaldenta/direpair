<?php

declare(strict_types=1);

namespace App;

enum RepairStatus: string
{
    case Submitted = 'submitted';
    case AwaitingUnit = 'awaiting_unit';
    case UnitReceived = 'unit_received';
    case Diagnosing = 'diagnosing';
    case AwaitingApproval = 'awaiting_approval';
    case Approved = 'approved';
    case AwaitingDeposit = 'awaiting_deposit';
    case Repairing = 'repairing';
    case QualityControl = 'quality_control';
    case AwaitingFinalPayment = 'awaiting_final_payment';
    case ReadyForHandover = 'ready_for_handover';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case WarrantyReview = 'warranty_review';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Permintaan diterima',
            self::AwaitingUnit => 'Menunggu unit',
            self::UnitReceived => 'Unit diterima',
            self::Diagnosing => 'Sedang didiagnosis',
            self::AwaitingApproval => 'Menunggu persetujuan estimasi',
            self::Approved => 'Estimasi disetujui',
            self::AwaitingDeposit => 'Menunggu pembayaran deposit',
            self::Repairing => 'Sedang diperbaiki',
            self::QualityControl => 'Pemeriksaan kualitas',
            self::AwaitingFinalPayment => 'Menunggu pelunasan',
            self::ReadyForHandover => 'Siap diserahkan',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
            self::WarrantyReview => 'Pemeriksaan garansi',
        };
    }
}
