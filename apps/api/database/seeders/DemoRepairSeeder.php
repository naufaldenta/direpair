<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Diagnosis;
use App\Models\Quotation;
use App\Models\ServiceRequest;
use App\Models\User;
use App\PaymentStatus;
use App\QuotationStatus;
use App\RepairStatus;
use Illuminate\Database\Seeder;

final class DemoRepairSeeder extends Seeder
{
    public const STATUS_TOKEN = 'demo-status-token-direpair-local-123456789';

    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $user = User::query()->updateOrCreate(
            ['email' => (string) config('direpair.demo_admin.email')],
            [
                'name' => 'Direpair Demo Admin',
                'password' => (string) config('direpair.demo_admin.password'),
                'email_verified_at' => now(),
            ],
        );
        $customer = Customer::query()->updateOrCreate(
            ['uuid' => '11111111-1111-4111-8111-111111111111'],
            [
                'name' => '[Demo] Raka Pelanggan',
                'phone' => '+6281234567890',
                'email' => 'customer@demo.invalid',
                'whatsapp_consent' => true,
                'privacy_consent_at' => now(),
                'privacy_consent_version' => 'demo-v1',
            ],
        );
        $serviceRequest = ServiceRequest::query()->updateOrCreate(
            ['uuid' => '22222222-2222-4222-8222-222222222222'],
            [
                'customer_id' => $customer->id,
                'public_number' => 'DRP-DEMO-001',
                'public_token_hash' => hash_hmac('sha256', self::STATUS_TOKEN, (string) config('direpair.token_pepper')),
                'service_slug' => 'tws',
                'device_category' => 'TWS & Wireless Earbuds',
                'brand' => 'Demo Brand',
                'model' => 'Demo Buds X',
                'serial_number' => null,
                'symptom' => 'Unit sebelah kanan tidak mengisi dan mati setelah digunakan beberapa menit.',
                'preferred_service_method' => 'drop-off',
                'service_address' => null,
                'urgency' => 'normal',
                'status' => RepairStatus::AwaitingApproval,
                'payment_status' => PaymentStatus::NotRequired,
                'source' => 'demo-seeder',
                'is_demo' => true,
                'submitted_at' => now()->subDays(2),
            ],
        );
        $diagnosis = Diagnosis::query()->updateOrCreate(
            ['service_request_id' => $serviceRequest->id, 'version' => 1],
            [
                'created_by' => $user->id,
                'repairability' => 'repairable',
                'summary' => 'Fixture diagnosis: jalur charging earbud kanan perlu diperbaiki dan baterai diuji ulang.',
                'findings' => ['summary' => 'Konten demo untuk pengujian UI, bukan diagnosis pelanggan nyata.'],
                'estimated_days' => 4,
                'internal_notes' => 'DEMO ONLY',
                'diagnosed_at' => now()->subDay(),
            ],
        );
        $quotation = Quotation::query()->updateOrCreate(
            ['uuid' => '33333333-3333-4333-8333-333333333333'],
            [
                'service_request_id' => $serviceRequest->id,
                'diagnosis_id' => $diagnosis->id,
                'created_by' => $user->id,
                'version' => 1,
                'status' => QuotationStatus::Sent,
                'currency' => 'IDR',
                'subtotal' => 400000,
                'discount' => 0,
                'tax' => 0,
                'total' => 400000,
                'deposit_required' => true,
                'deposit_amount' => 150000,
                'customer_notes' => 'Nominal ini adalah fixture demo dan tidak mewakili harga Direpair sebenarnya.',
                'sent_at' => now()->subHours(12),
                'expires_at' => now()->addDays(7),
            ],
        );
        $quotation->items()->delete();
        $quotation->items()->createMany([
            [
                'type' => 'diagnosis',
                'label' => 'Diagnosis teknis (demo)',
                'quantity' => 1,
                'unit_price' => 50000,
                'total' => 50000,
                'sort_order' => 0,
            ],
            [
                'type' => 'labor',
                'label' => 'Jasa repair jalur charging (demo)',
                'quantity' => 1,
                'unit_price' => 350000,
                'total' => 350000,
                'sort_order' => 1,
            ],
        ]);
        $serviceRequest->statusEvents()->delete();
        $serviceRequest->statusEvents()->createMany([
            [
                'from_status' => null,
                'to_status' => RepairStatus::Submitted->value,
                'public_label' => RepairStatus::Submitted->label(),
                'public_message' => 'Fixture demo: permintaan diterima.',
                'visible_to_customer' => true,
                'occurred_at' => now()->subDays(2),
            ],
            [
                'actor_user_id' => $user->id,
                'from_status' => RepairStatus::Diagnosing->value,
                'to_status' => RepairStatus::AwaitingApproval->value,
                'public_label' => RepairStatus::AwaitingApproval->label(),
                'public_message' => 'Fixture demo: diagnosis dan quotation siap ditinjau.',
                'visible_to_customer' => true,
                'occurred_at' => now()->subHours(12),
            ],
        ]);
    }
}
