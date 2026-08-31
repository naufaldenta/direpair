<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\ServiceRequest;
use App\PaymentStatus;
use App\RepairStatus;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class CreateServiceRequestAction
{
    /**
     * @param  array<string, mixed>  $data
     * @param  list<UploadedFile>  $attachments
     * @return array{service_request: ServiceRequest, status_token: string}
     */
    public function execute(array $data, array $attachments, ?string $ip, ?string $userAgent): array
    {
        $privacyPolicyVersion = (string) config('direpair.privacy_policy_version');

        if (app()->environment('production') && ($privacyPolicyVersion === '' || str_starts_with($privacyPolicyVersion, 'draft'))) {
            throw ValidationException::withMessages([
                'privacy_consent' => 'Booking belum tersedia sampai kebijakan privasi production disahkan.',
            ]);
        }

        $storedPaths = [];
        $statusToken = Str::random(48);

        try {
            return DB::transaction(function () use (
                $data,
                $attachments,
                $ip,
                $userAgent,
                $statusToken,
                $privacyPolicyVersion,
                &$storedPaths,
            ): array {
                $customer = Customer::query()->create([
                    'uuid' => (string) Str::uuid(),
                    'name' => trim((string) $data['name']),
                    'phone' => $this->normalizePhone((string) $data['phone']),
                    'email' => $data['email'] ?? null,
                    'whatsapp_consent' => (bool) ($data['whatsapp_consent'] ?? false),
                    'privacy_consent_at' => now(),
                    'privacy_consent_version' => $privacyPolicyVersion,
                ]);
                $serviceRequest = ServiceRequest::query()->create([
                    'uuid' => (string) Str::uuid(),
                    'customer_id' => $customer->id,
                    'public_number' => $this->newPublicNumber(),
                    'public_token_hash' => $this->hashToken($statusToken),
                    'service_slug' => (string) $data['service_slug'],
                    'device_category' => (string) $data['device_category'],
                    'brand' => $data['brand'] ?? null,
                    'model' => $data['model'] ?? null,
                    'serial_number' => $data['serial_number'] ?? null,
                    'symptom' => (string) $data['symptom'],
                    'preferred_service_method' => (string) $data['preferred_service_method'],
                    'service_address' => $data['service_address'] ?? null,
                    'urgency' => $data['urgency'] ?? 'normal',
                    'status' => RepairStatus::Submitted,
                    'payment_status' => PaymentStatus::NotRequired,
                    'source' => 'website',
                    'is_demo' => false,
                    'submitted_at' => now(),
                ]);

                foreach ($attachments as $file) {
                    $filename = (string) Str::uuid().'.'.($file->guessExtension() ?: 'bin');
                    $path = $file->storeAs('service-requests/'.$serviceRequest->uuid, $filename, 'private');

                    if (! is_string($path)) {
                        throw new \RuntimeException('Failed to store booking attachment.');
                    }

                    $storedPaths[] = $path;
                    $serviceRequest->media()->create([
                        'disk' => 'private',
                        'path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                        'size_bytes' => $file->getSize(),
                        'is_demo' => false,
                    ]);
                }

                $serviceRequest->statusEvents()->create([
                    'from_status' => null,
                    'to_status' => RepairStatus::Submitted->value,
                    'public_label' => RepairStatus::Submitted->label(),
                    'public_message' => 'Detail perangkat sudah tercatat. Tim Direpair akan mengonfirmasi langkah berikutnya.',
                    'visible_to_customer' => true,
                    'occurred_at' => now(),
                ]);
                AuditLog::query()->create([
                    'subject_type' => ServiceRequest::class,
                    'subject_id' => $serviceRequest->id,
                    'action' => 'repair.submitted',
                    'after' => [
                        'status' => RepairStatus::Submitted->value,
                        'source' => 'website',
                        'attachment_count' => count($storedPaths),
                    ],
                    'ip_hash' => $this->hashIdentifier($ip),
                    'user_agent_hash' => $this->hashIdentifier($userAgent),
                ]);

                return [
                    'service_request' => $serviceRequest->load('statusEvents'),
                    'status_token' => $statusToken,
                ];
            });
        } catch (\Throwable $exception) {
            if ($storedPaths !== []) {
                Storage::disk('private')->delete($storedPaths);
            }

            throw $exception;
        }
    }

    private function newPublicNumber(): string
    {
        do {
            $number = 'DRP-'.now()->format('ymd').'-'.Str::upper(Str::random(6));
        } while (ServiceRequest::query()->where('public_number', $number)->exists());

        return $number;
    }

    private function normalizePhone(string $phone): string
    {
        $normalized = preg_replace('/[^0-9+]/', '', trim($phone)) ?: trim($phone);

        return str_starts_with($normalized, '0') ? '+62'.substr($normalized, 1) : $normalized;
    }

    private function hashToken(string $token): string
    {
        return hash_hmac('sha256', $token, (string) config('direpair.token_pepper'));
    }

    private function hashIdentifier(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return hash_hmac('sha256', $value, (string) config('direpair.token_pepper'));
    }
}
