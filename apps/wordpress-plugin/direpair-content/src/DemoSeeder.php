<?php

declare(strict_types=1);

namespace Direpair\Content;

final class DemoSeeder
{
    private const DEMO_KEY = '_direpair_demo_key';

    public static function registerCli(): void
    {
        if (! defined('WP_CLI') || ! WP_CLI) {
            return;
        }

        \WP_CLI::add_command('direpair seed-demo', [self::class, 'command']);
    }

    /**
     * @param list<string> $arguments
     * @param array<string, mixed> $options
     */
    public static function command(array $arguments, array $options): void
    {
        unset($arguments);

        if (wp_get_environment_type() === 'production' && ! isset($options['force'])) {
            \WP_CLI::error('Refusing to seed demo content in production without --force.');
        }

        $counts = self::seed();
        \WP_CLI::success(sprintf(
            'Seeded %d services, %d problems, and %d supporting entries.',
            $counts['services'],
            $counts['problems'],
            $counts['supporting']
        ));
    }

    /**
     * @return array{services: int, problems: int, supporting: int}
     */
    public static function seed(): array
    {
        self::seedTerms();

        $serviceDefinitions = [
            'service-tws' => [
                'title' => 'Service TWS & Wireless Earbuds',
                'slug' => 'tws',
                'excerpt' => 'Diagnosis dan repair TWS berbagai brand untuk masalah charging, baterai, koneksi, dan audio.',
                'content' => '<h2>Kerusakan yang kami tangani</h2><p>Contoh konten demo untuk masalah mati sebelah, tidak mengisi, baterai cepat habis, dan charging case.</p>',
                'device' => 'tws',
                'meta' => [
                    'quick_answer' => 'Sebagian kerusakan TWS dapat diperbaiki setelah penyebabnya dipastikan melalui diagnosis.',
                    'price_from' => 150000,
                    'price_to' => 650000,
                    'diagnosis_fee' => 50000,
                    'turnaround_min_days' => 2,
                    'turnaround_max_days' => 7,
                    'supported_models' => ['Sony WF series', 'JBL Tune series', 'Soundcore Liberty series'],
                    'service_methods' => ['drop-off', 'pickup'],
                ],
            ],
            'service-vacuum' => [
                'title' => 'Service Vacuum Cleaner',
                'slug' => 'vacuum',
                'excerpt' => 'Diagnosis vacuum cordless dan robot vacuum untuk masalah daya, charging, baterai, dan motor.',
                'content' => '<h2>Vacuum cordless dan robot vacuum</h2><p>Konten ini adalah fixture demo dan dapat diganti dari WordPress.</p>',
                'device' => 'vacuum',
                'meta' => [
                    'quick_answer' => 'Vacuum yang tidak menyala atau melemah perlu diperiksa pada baterai, charger, airflow, motor, dan control board.',
                    'price_from' => 175000,
                    'price_to' => 950000,
                    'diagnosis_fee' => 75000,
                    'turnaround_min_days' => 2,
                    'turnaround_max_days' => 10,
                    'service_methods' => ['drop-off', 'pickup'],
                ],
            ],
            'service-household' => [
                'title' => 'Service Household Electronics',
                'slug' => 'household',
                'excerpt' => 'Service perangkat rumah tangga non-mainstream seperti air purifier, humidifier, dan coffee machine.',
                'content' => '<p>Gunakan form cek kerusakan bila jenis perangkat belum tersedia di daftar.</p>',
                'device' => 'household',
                'meta' => [
                    'quick_answer' => 'Kelayakan repair bergantung pada diagnosis dan ketersediaan komponen.',
                    'diagnosis_fee' => 75000,
                    'turnaround_min_days' => 3,
                    'turnaround_max_days' => 14,
                    'service_methods' => ['drop-off', 'pickup'],
                ],
            ],
            'service-ebike' => [
                'title' => 'Service Sepeda Listrik',
                'slug' => 'sepeda-listrik',
                'excerpt' => 'Diagnosis baterai, charger, controller, wiring, motor, dan sistem kelistrikan sepeda listrik.',
                'content' => '<h2>Home service dan workshop</h2><p>Ketersediaan metode service mengikuti jenis kerusakan dan area layanan.</p>',
                'device' => 'sepeda-listrik',
                'meta' => [
                    'quick_answer' => 'Sepeda listrik yang tidak jalan perlu diperiksa secara berurutan dari baterai, charger, controller, wiring, hingga motor.',
                    'price_from' => 200000,
                    'price_to' => 2500000,
                    'diagnosis_fee' => 100000,
                    'turnaround_min_days' => 1,
                    'turnaround_max_days' => 14,
                    'service_methods' => ['drop-off', 'pickup', 'home-service'],
                ],
            ],
            'service-other' => [
                'title' => 'Service Produk Lainnya',
                'slug' => 'lainnya',
                'excerpt' => 'Tidak menemukan perangkat kamu? Kirim detail dan foto untuk pemeriksaan awal.',
                'content' => '<p>Halaman demand discovery untuk perangkat elektronik yang belum mempunyai kategori khusus.</p>',
                'device' => 'lainnya',
                'meta' => [
                    'quick_answer' => 'Tim Direpair akan menilai apakah perangkat dapat masuk ke proses diagnosis.',
                    'service_methods' => ['drop-off', 'pickup'],
                ],
            ],
        ];

        $serviceIds = [];

        foreach ($serviceDefinitions as $key => $definition) {
            $serviceIds[$definition['slug']] = self::upsertPost('direpair_service', $key, $definition);
        }

        $problemDefinitions = [
            'problem-tws-mati-sebelah' => [
                'title' => 'TWS Mati Sebelah: Penyebab, Diagnosis & Service',
                'slug' => 'tws-mati-sebelah',
                'excerpt' => 'Kemungkinan penyebab dan langkah aman sebelum TWS dibawa untuk diagnosis.',
                'content' => '<h2>Kemungkinan penyebab</h2><p>Baterai, charging contact, driver, PCB, firmware, atau komponen lain.</p>',
                'device' => 'tws',
                'service_slug' => 'tws',
            ],
            'problem-tws-tidak-charging' => [
                'title' => 'TWS Tidak Bisa Di-charge',
                'slug' => 'tws-tidak-charging',
                'excerpt' => 'Diagnosis charging contact, case, baterai, dan power circuit.',
                'content' => '<p>Jangan memaksa charging bila perangkat panas, berbau, atau baterai mengembung.</p>',
                'device' => 'tws',
                'service_slug' => 'tws',
            ],
            'problem-vacuum-tidak-menyala' => [
                'title' => 'Vacuum Tidak Menyala',
                'slug' => 'vacuum-tidak-menyala',
                'excerpt' => 'Pemeriksaan baterai, charger, switch, motor, sensor, dan control board.',
                'content' => '<p>Diagnosis menentukan apakah masalah berasal dari sumber daya atau rangkaian internal.</p>',
                'device' => 'vacuum',
                'service_slug' => 'vacuum',
            ],
            'problem-ebike-tidak-jalan' => [
                'title' => 'Sepeda Listrik Tidak Jalan',
                'slug' => 'sepeda-listrik-tidak-jalan',
                'excerpt' => 'Baterai penuh tetapi kendaraan tidak bergerak dapat melibatkan controller, wiring, throttle, brake cut-off, atau motor.',
                'content' => '<p>Pemeriksaan berurutan menghindari penggantian komponen yang tidak diperlukan.</p>',
                'device' => 'sepeda-listrik',
                'service_slug' => 'sepeda-listrik',
            ],
        ];

        $problemIdsByService = [];

        foreach ($problemDefinitions as $key => $definition) {
            $serviceId = $serviceIds[$definition['service_slug']];
            $definition['meta'] = [
                'quick_answer' => $definition['excerpt'],
                'related_service_ids' => [$serviceId],
            ];
            $problemId = self::upsertPost('direpair_problem', $key, $definition);
            $problemIdsByService[$definition['service_slug']][] = $problemId;
        }

        foreach ($problemIdsByService as $serviceSlug => $problemIds) {
            update_post_meta($serviceIds[$serviceSlug], MetaFields::key('related_problem_ids'), $problemIds);
        }

        $supporting = 0;
        $supporting += self::seedSupportingContent($serviceIds);

        if (get_option(Settings::PUBLIC_OPTION, null) === null) {
            update_option(Settings::PUBLIC_OPTION, Settings::defaults());
        }

        return [
            'services' => count($serviceDefinitions),
            'problems' => count($problemDefinitions),
            'supporting' => $supporting,
        ];
    }

    private static function seedTerms(): void
    {
        $terms = [
            'direpair_device' => [
                'tws' => 'TWS & Wireless Earbuds',
                'vacuum' => 'Vacuum & Cleaning Tech',
                'household' => 'Household Electronics',
                'sepeda-listrik' => 'Sepeda Listrik',
                'lainnya' => 'Produk Lainnya',
            ],
            'direpair_method' => [
                'drop-off' => 'Drop-off',
                'pickup' => 'Pickup',
                'home-service' => 'Home Service',
            ],
        ];

        foreach ($terms as $taxonomy => $taxonomyTerms) {
            foreach ($taxonomyTerms as $slug => $name) {
                if (get_term_by('slug', $slug, $taxonomy) === false) {
                    wp_insert_term($name, $taxonomy, ['slug' => $slug]);
                }
            }
        }
    }

    /**
     * @param array<string, int> $serviceIds
     */
    private static function seedSupportingContent(array $serviceIds): int
    {
        $entries = [
            'demo-technician' => [
                'post_type' => 'direpair_tech',
                'title' => '[Demo] Teknisi Direpair',
                'slug' => 'demo-teknisi',
                'excerpt' => 'Profil contoh untuk menguji layout. Ganti dengan profil teknisi nyata sebelum dipublikasikan.',
                'content' => '<p>Konten demo. Jangan gunakan sebagai klaim pengalaman atau sertifikasi.</p>',
                'meta' => [
                    'specialties' => ['TWS', 'Vacuum', 'Sepeda listrik'],
                    'related_service_ids' => array_values($serviceIds),
                ],
            ],
            'demo-location' => [
                'post_type' => 'direpair_location',
                'title' => '[Demo] Workshop Direpair',
                'slug' => 'demo-workshop',
                'excerpt' => 'Lokasi demo untuk staging. Ganti dengan lokasi nyata sebelum local SEO diaktifkan.',
                'content' => '<p>Alamat ini bukan lokasi bisnis nyata.</p>',
                'meta' => [
                    'city' => 'Jakarta',
                    'region' => 'DKI Jakarta',
                    'service_methods' => ['drop-off', 'pickup'],
                    'related_service_ids' => array_values($serviceIds),
                ],
            ],
            'demo-faq' => [
                'post_type' => 'direpair_faq',
                'title' => 'Bagaimana mengetahui apakah perangkat bisa diperbaiki?',
                'slug' => 'apakah-perangkat-bisa-diperbaiki',
                'excerpt' => 'Direpair melakukan pemeriksaan awal dan diagnosis sebelum pekerjaan disetujui.',
                'content' => '<p>Repairability bergantung pada penyebab kerusakan, kondisi unit, dan ketersediaan komponen.</p>',
                'meta' => [
                    'related_service_ids' => array_values($serviceIds),
                ],
            ],
            'demo-warranty' => [
                'post_type' => 'direpair_warranty',
                'title' => '[Demo] Kebijakan Garansi Service',
                'slug' => 'demo-garansi-service',
                'excerpt' => 'Draft contoh yang harus direview bisnis/legal sebelum launch.',
                'content' => '<p>Garansi mengikuti jenis pekerjaan dan komponen yang digunakan.</p>',
                'meta' => [
                    'warranty_duration_days' => 30,
                    'warranty_summary' => 'Contoh garansi 30 hari untuk pekerjaan yang tercantum pada invoice.',
                    'warranty_exclusions' => ['Kerusakan fisik baru', 'Kerusakan cairan baru', 'Modifikasi pihak ketiga'],
                    'related_service_ids' => array_values($serviceIds),
                ],
            ],
            'demo-privacy-policy' => [
                'post_type' => 'direpair_policy',
                'title' => '[Demo] Kebijakan Privasi',
                'slug' => 'privacy',
                'excerpt' => 'Draft struktur privasi untuk ditinjau dan diganti klien sebelum pengumpulan data production.',
                'content' => '<h2>Data yang dikumpulkan</h2><p>Booking memerlukan data kontak, detail perangkat, keluhan, consent, dan lampiran opsional untuk menjalankan layanan repair.</p><h2>Penggunaan dan penyimpanan</h2><p>Data operasional disimpan di sistem Laravel, bukan WordPress. Isi retention, hak subjek data, dasar pemrosesan, vendor, dan kanal permintaan sesuai kebijakan final.</p>',
                'meta' => [],
            ],
            'demo-terms-policy' => [
                'post_type' => 'direpair_policy',
                'title' => '[Demo] Syarat Layanan',
                'slug' => 'terms',
                'excerpt' => 'Draft struktur syarat layanan untuk ditinjau bisnis/legal sebelum launch.',
                'content' => '<h2>Diagnosis dan quotation</h2><p>Booking bukan persetujuan repair. Pekerjaan mengikuti diagnosis, quotation aktif, dan keputusan pelanggan.</p><h2>Pembayaran dan garansi</h2><p>Isi aturan diagnosis fee, deposit, pelunasan, pembatalan, penyimpanan unit, serah terima, dan garansi sesuai kebijakan final Direpair.</p>',
                'meta' => [],
            ],
            'demo-case' => [
                'post_type' => 'direpair_case',
                'title' => '[Demo] Repair TWS Tidak Mengisi',
                'slug' => 'demo-repair-tws-tidak-mengisi',
                'excerpt' => 'Kasus fixture untuk staging, bukan bukti repair nyata.',
                'content' => '<p>Ganti dengan kasus repair nyata yang telah mempunyai izin publikasi.</p>',
                'meta' => [
                    'device_brand' => 'Demo Brand',
                    'device_model' => 'Demo Model',
                    'reported_symptom' => 'Tidak mengisi',
                    'diagnosis_summary' => 'Fixture diagnosis untuk pengujian layout.',
                    'repair_action' => 'Fixture repair action.',
                    'repair_outcome' => 'Demo only',
                    'publish_consent' => false,
                    'related_service_ids' => [$serviceIds['tws']],
                ],
            ],
        ];

        foreach ($serviceIds as $slug => $serviceId) {
            $entries['demo-price-'.$slug] = [
                'post_type' => 'direpair_price',
                'title' => '[Demo] Estimasi '.get_the_title($serviceId),
                'slug' => 'demo-estimasi-'.$slug,
                'excerpt' => 'Kisaran demo untuk menguji komponen pricing.',
                'content' => '<p>Harga final diberikan setelah diagnosis dan sebelum pekerjaan dimulai.</p>',
                'meta' => [
                    'related_service_ids' => [$serviceId],
                    'pricing_disclaimer' => 'Nilai ini adalah dummy staging dan harus diganti atau dikosongkan sebelum launch.',
                    'currency' => 'IDR',
                ],
            ];
        }

        foreach ($entries as $key => $entry) {
            self::upsertPost($entry['post_type'], $key, $entry);
        }

        return count($entries);
    }

    /**
     * @param array<string, mixed> $definition
     */
    private static function upsertPost(string $postType, string $demoKey, array $definition): int
    {
        $existing = get_posts([
            'post_type' => $postType,
            'post_status' => 'any',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_key' => self::DEMO_KEY,
            'meta_value' => $demoKey,
        ]);
        $postId = $existing === [] ? 0 : (int) $existing[0];
        $status = wp_get_environment_type() === 'production' ? 'draft' : 'publish';
        $postData = [
            'ID' => $postId,
            'post_type' => $postType,
            'post_status' => $status,
            'post_title' => (string) $definition['title'],
            'post_name' => (string) $definition['slug'],
            'post_excerpt' => (string) ($definition['excerpt'] ?? ''),
            'post_content' => (string) ($definition['content'] ?? ''),
        ];
        $result = wp_insert_post($postData, true);

        if (is_wp_error($result)) {
            throw new \RuntimeException($result->get_error_message());
        }

        $postId = (int) $result;
        update_post_meta($postId, self::DEMO_KEY, $demoKey);
        update_post_meta($postId, MetaFields::key('external_uuid'), self::demoUuid($demoKey));
        update_post_meta($postId, MetaFields::key('is_demo'), true);
        update_post_meta($postId, MetaFields::key('is_verified'), false);
        update_post_meta($postId, MetaFields::key('noindex'), true);

        foreach (($definition['meta'] ?? []) as $name => $value) {
            update_post_meta($postId, MetaFields::key((string) $name), $value);
        }

        if (isset($definition['device'])) {
            $term = get_term_by('slug', (string) $definition['device'], 'direpair_device');

            if ($term instanceof \WP_Term) {
                wp_set_object_terms($postId, [$term->term_id], 'direpair_device');
            }
        }

        return $postId;
    }

    private static function demoUuid(string $demoKey): string
    {
        $hash = hash('sha256', 'direpair-demo-'.$demoKey);

        return sprintf(
            '%s-%s-4%s-8%s-%s',
            substr($hash, 0, 8),
            substr($hash, 8, 4),
            substr($hash, 13, 3),
            substr($hash, 17, 3),
            substr($hash, 20, 12)
        );
    }
}
