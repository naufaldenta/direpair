<?php

declare(strict_types=1);

namespace Direpair\Content;

final class MetaFields
{
    public const PREFIX = '_direpair_';

    /**
     * @return array<string, array{label: string, type: string, control?: string, default?: mixed, post_types?: list<string>, description?: string, placeholder?: string, relation_post_type?: string, taxonomy?: string}>
     */
    public static function definitions(): array
    {
        $allPostTypes = array_keys(ContentTypes::postTypes());
        $serviceContent = ['direpair_service', 'direpair_problem', 'direpair_price'];

        return [
            'external_uuid' => [
                'label' => 'ID sistem',
                'type' => 'string',
                'control' => 'readonly',
                'post_types' => $allPostTypes,
            ],
            'is_demo' => [
                'label' => 'Ini masih konten demo',
                'type' => 'boolean',
                'control' => 'checkbox',
                'default' => false,
                'post_types' => $allPostTypes,
                'description' => 'Konten demo tidak akan ditampilkan pada website production.',
            ],
            'is_verified' => [
                'label' => 'Sudah diperiksa dan boleh tayang',
                'type' => 'boolean',
                'control' => 'checkbox',
                'default' => false,
                'post_types' => $allPostTypes,
                'description' => 'Centang setelah fakta, harga, identitas, dan izin publikasi sudah benar.',
            ],
            'noindex' => [
                'label' => 'Sembunyikan dari mesin pencari',
                'type' => 'boolean',
                'control' => 'checkbox',
                'default' => false,
                'post_types' => $allPostTypes,
                'description' => 'Gunakan untuk draft publik yang boleh dibuka lewat tautan tetapi belum boleh masuk Google.',
            ],
            'seo_title' => [
                'label' => 'Judul untuk Google',
                'type' => 'string',
                'post_types' => $allPostTypes,
                'description' => 'Opsional. Kosongkan untuk memakai judul utama.',
            ],
            'seo_description' => [
                'label' => 'Deskripsi untuk Google',
                'type' => 'string',
                'control' => 'textarea',
                'post_types' => $allPostTypes,
                'description' => 'Ringkas manfaat halaman dalam satu atau dua kalimat.',
            ],
            'hero_eyebrow' => [
                'label' => 'Label kecil di bagian atas',
                'type' => 'string',
                'post_types' => $allPostTypes,
            ],
            'hero_title' => [
                'label' => 'Judul tampilan utama',
                'type' => 'string',
                'post_types' => $allPostTypes,
                'description' => 'Opsional. Kosongkan untuk memakai judul konten.',
            ],
            'hero_summary' => [
                'label' => 'Ringkasan tampilan utama',
                'type' => 'string',
                'control' => 'textarea',
                'post_types' => $allPostTypes,
            ],
            'primary_cta_label' => [
                'label' => 'Teks tombol utama',
                'type' => 'string',
                'post_types' => $allPostTypes,
            ],
            'primary_cta_url' => [
                'label' => 'Tujuan tombol utama',
                'type' => 'string',
                'control' => 'url',
                'post_types' => $allPostTypes,
                'placeholder' => '/booking/',
            ],
            'quick_answer' => [
                'label' => 'Jawaban cepat',
                'type' => 'string',
                'control' => 'textarea',
                'post_types' => ['direpair_service', 'direpair_problem', 'direpair_article'],
            ],
            'price_from' => [
                'label' => 'Harga mulai dari',
                'type' => 'number',
                'post_types' => $serviceContent,
                'description' => 'Isi angka tanpa Rp dan tanpa pemisah ribuan.',
            ],
            'price_to' => [
                'label' => 'Harga sampai dengan',
                'type' => 'number',
                'post_types' => $serviceContent,
                'description' => 'Kosongkan bila tidak ada batas atas yang dapat dipastikan.',
            ],
            'diagnosis_fee' => [
                'label' => 'Biaya diagnosis',
                'type' => 'number',
                'post_types' => $serviceContent,
            ],
            'currency' => [
                'label' => 'Mata uang',
                'type' => 'string',
                'default' => 'IDR',
                'post_types' => $serviceContent,
            ],
            'turnaround_min_days' => [
                'label' => 'Estimasi tercepat (hari)',
                'type' => 'integer',
                'post_types' => $serviceContent,
            ],
            'turnaround_max_days' => [
                'label' => 'Estimasi terlama (hari)',
                'type' => 'integer',
                'post_types' => $serviceContent,
            ],
            'pricing_disclaimer' => [
                'label' => 'Catatan harga',
                'type' => 'string',
                'control' => 'textarea',
                'post_types' => $serviceContent,
            ],
            'warranty_summary' => [
                'label' => 'Ringkasan garansi',
                'type' => 'string',
                'control' => 'textarea',
                'post_types' => ['direpair_service', 'direpair_problem', 'direpair_price', 'direpair_warranty'],
            ],
            'supported_models' => [
                'label' => 'Model yang didukung',
                'type' => 'array_string',
                'control' => 'textarea_list',
                'post_types' => ['direpair_service', 'direpair_problem'],
                'description' => 'Tulis satu model per baris.',
            ],
            'related_problem_ids' => [
                'label' => 'Panduan masalah terkait',
                'type' => 'array_integer',
                'control' => 'post_select',
                'post_types' => ['direpair_service'],
                'relation_post_type' => 'direpair_problem',
                'description' => 'Pilih panduan masalah yang ingin ditampilkan bersama layanan ini.',
            ],
            'related_service_ids' => [
                'label' => 'Layanan terkait',
                'type' => 'array_integer',
                'control' => 'post_select',
                'post_types' => ['direpair_problem', 'direpair_location', 'direpair_tech', 'direpair_case', 'direpair_price', 'direpair_faq', 'direpair_warranty', 'direpair_article'],
                'relation_post_type' => 'direpair_service',
                'description' => 'Pilih layanan yang berhubungan. Tidak perlu mengetik ID.',
            ],
            'related_technician_ids' => [
                'label' => 'Teknisi terkait',
                'type' => 'array_integer',
                'control' => 'post_select',
                'post_types' => ['direpair_service', 'direpair_problem', 'direpair_location', 'direpair_case', 'direpair_article'],
                'relation_post_type' => 'direpair_tech',
            ],
            'faq_ids' => [
                'label' => 'FAQ terkait',
                'type' => 'array_integer',
                'control' => 'post_select',
                'post_types' => ['direpair_service', 'direpair_problem', 'direpair_location', 'direpair_price', 'direpair_warranty'],
                'relation_post_type' => 'direpair_faq',
            ],
            'street_address' => [
                'label' => 'Alamat lengkap',
                'type' => 'string',
                'post_types' => ['direpair_location'],
            ],
            'city' => [
                'label' => 'Kota',
                'type' => 'string',
                'post_types' => ['direpair_location'],
            ],
            'region' => [
                'label' => 'Provinsi',
                'type' => 'string',
                'post_types' => ['direpair_location'],
            ],
            'postal_code' => [
                'label' => 'Kode pos',
                'type' => 'string',
                'post_types' => ['direpair_location'],
            ],
            'phone' => [
                'label' => 'Nomor telepon',
                'type' => 'string',
                'post_types' => ['direpair_location'],
            ],
            'whatsapp' => [
                'label' => 'WhatsApp',
                'type' => 'string',
                'post_types' => ['direpair_location'],
            ],
            'latitude' => [
                'label' => 'Latitude',
                'type' => 'number',
                'post_types' => ['direpair_location'],
            ],
            'longitude' => [
                'label' => 'Longitude',
                'type' => 'number',
                'post_types' => ['direpair_location'],
            ],
            'opening_hours' => [
                'label' => 'Jam operasional',
                'type' => 'array_string',
                'control' => 'textarea_list',
                'post_types' => ['direpair_location'],
                'description' => 'Satu baris per hari. Contoh: Senin|09:00|18:00.',
            ],
            'specialties' => [
                'label' => 'Spesialisasi',
                'type' => 'array_string',
                'control' => 'textarea_list',
                'post_types' => ['direpair_tech'],
            ],
            'years_experience' => [
                'label' => 'Lama pengalaman (tahun)',
                'type' => 'integer',
                'post_types' => ['direpair_tech'],
            ],
            'certifications' => [
                'label' => 'Sertifikasi yang sudah diverifikasi',
                'type' => 'array_string',
                'control' => 'textarea_list',
                'post_types' => ['direpair_tech'],
            ],
            'device_brand' => [
                'label' => 'Merek perangkat',
                'type' => 'string',
                'post_types' => ['direpair_case'],
            ],
            'device_model' => [
                'label' => 'Model perangkat',
                'type' => 'string',
                'post_types' => ['direpair_case'],
            ],
            'reported_symptom' => [
                'label' => 'Gejala yang dilaporkan',
                'type' => 'string',
                'control' => 'textarea',
                'post_types' => ['direpair_case'],
            ],
            'diagnosis_summary' => [
                'label' => 'Ringkasan diagnosis',
                'type' => 'string',
                'control' => 'textarea',
                'post_types' => ['direpair_case'],
            ],
            'repair_action' => [
                'label' => 'Tindakan perbaikan',
                'type' => 'string',
                'control' => 'textarea',
                'post_types' => ['direpair_case'],
            ],
            'repair_outcome' => [
                'label' => 'Hasil perbaikan',
                'type' => 'string',
                'post_types' => ['direpair_case'],
            ],
            'publish_consent' => [
                'label' => 'Pelanggan sudah mengizinkan publikasi',
                'type' => 'boolean',
                'control' => 'checkbox',
                'default' => false,
                'post_types' => ['direpair_case'],
                'description' => 'Wajib dicentang sebelum kasus nyata boleh tampil di website.',
            ],
            'warranty_duration_days' => [
                'label' => 'Durasi garansi (hari)',
                'type' => 'integer',
                'post_types' => ['direpair_warranty'],
            ],
            'warranty_exclusions' => [
                'label' => 'Pengecualian garansi',
                'type' => 'array_string',
                'control' => 'textarea_list',
                'post_types' => ['direpair_warranty'],
            ],
            'reviewed_at' => [
                'label' => 'Tanggal terakhir ditinjau teknisi',
                'type' => 'string',
                'control' => 'date',
                'post_types' => ['direpair_article'],
            ],
        ];
    }

    public static function register(): void
    {
        foreach (ContentTypes::postTypes() as $postType => $unused) {
            foreach (self::forPostType($postType) as $key => $definition) {
                register_post_meta($postType, self::key($key), self::registrationArgs($definition));
            }
        }
    }

    /**
     * @return array<string, array{label: string, type: string, control?: string, default?: mixed, post_types?: list<string>, description?: string, placeholder?: string, relation_post_type?: string, taxonomy?: string}>
     */
    public static function forPostType(string $postType): array
    {
        return array_filter(
            self::definitions(),
            static fn (array $definition): bool => in_array($postType, $definition['post_types'] ?? [], true)
        );
    }

    public static function key(string $name): string
    {
        return self::PREFIX.$name;
    }

    /**
     * @param array{type: string, default?: mixed} $definition
     * @return array<string, mixed>
     */
    private static function registrationArgs(array $definition): array
    {
        $type = $definition['type'];
        $default = $definition['default'] ?? self::defaultForType($type);

        if ($type === 'array_string' || $type === 'array_integer') {
            $itemType = $type === 'array_integer' ? 'integer' : 'string';

            return [
                'single' => true,
                'type' => 'array',
                'default' => [],
                'show_in_rest' => [
                    'schema' => [
                        'type' => 'array',
                        'items' => ['type' => $itemType],
                        'default' => [],
                    ],
                ],
                'sanitize_callback' => $itemType === 'integer'
                    ? [self::class, 'sanitizeIntegerArray']
                    : [self::class, 'sanitizeStringArray'],
                'auth_callback' => [self::class, 'canEditMeta'],
            ];
        }

        return [
            'single' => true,
            'type' => $type,
            'default' => $default,
            'show_in_rest' => true,
            'sanitize_callback' => match ($type) {
                'boolean' => [self::class, 'sanitizeBoolean'],
                'integer' => 'absint',
                'number' => [self::class, 'sanitizeNumber'],
                default => 'sanitize_text_field',
            },
            'auth_callback' => [self::class, 'canEditMeta'],
        ];
    }

    public static function canEditMeta(bool $allowed, string $metaKey, int $objectId): bool
    {
        unset($allowed, $metaKey);

        return current_user_can('edit_post', $objectId);
    }

    public static function sanitizeBoolean(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public static function sanitizeNumber(mixed $value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }

    /**
     * @return list<string>
     */
    public static function sanitizeStringArray(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn (mixed $item): string => sanitize_text_field((string) $item),
            $value
        )));
    }

    /**
     * @return list<int>
     */
    public static function sanitizeIntegerArray(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_map('absint', $value)));
    }

    private static function defaultForType(string $type): mixed
    {
        return match ($type) {
            'boolean' => false,
            'integer' => 0,
            'number' => 0.0,
            'array_string', 'array_integer' => [],
            default => '',
        };
    }
}
