<?php

declare(strict_types=1);

namespace Direpair\Content;

final class ContentTypes
{
    /**
     * @return array<string, array{label: string, singular: string, rest_base: string, menu_icon: string, title_placeholder: string, summary_label: string, content_label: string, form_intro: string}>
     */
    public static function postTypes(): array
    {
        return [
            'direpair_service' => [
                'label' => 'Layanan',
                'singular' => 'Layanan',
                'rest_base' => 'services',
                'menu_icon' => 'dashicons-admin-tools',
                'title_placeholder' => 'Contoh: Service TWS & Wireless Earbuds',
                'summary_label' => 'Ringkasan layanan',
                'content_label' => 'Penjelasan lengkap layanan',
                'form_intro' => 'Isi informasi layanan yang akan dibaca pelanggan. Harga final tetap diberikan setelah diagnosis.',
            ],
            'direpair_problem' => [
                'label' => 'Panduan Masalah',
                'singular' => 'Panduan Masalah',
                'rest_base' => 'problems',
                'menu_icon' => 'dashicons-search',
                'title_placeholder' => 'Contoh: TWS tidak bisa mengisi daya',
                'summary_label' => 'Ringkasan masalah',
                'content_label' => 'Panduan dan penjelasan lengkap',
                'form_intro' => 'Jelaskan gejala, kemungkinan penyebab, dan langkah aman tanpa menjanjikan hasil diagnosis.',
            ],
            'direpair_location' => [
                'label' => 'Lokasi',
                'singular' => 'Lokasi',
                'rest_base' => 'locations',
                'menu_icon' => 'dashicons-location-alt',
                'title_placeholder' => 'Contoh: Workshop Direpair Jakarta Selatan',
                'summary_label' => 'Ringkasan lokasi',
                'content_label' => 'Petunjuk dan informasi lokasi',
                'form_intro' => 'Masukkan hanya alamat dan jam operasional yang sudah dipastikan benar.',
            ],
            'direpair_tech' => [
                'label' => 'Teknisi',
                'singular' => 'Teknisi',
                'rest_base' => 'technicians',
                'menu_icon' => 'dashicons-businessperson',
                'title_placeholder' => 'Nama teknisi',
                'summary_label' => 'Ringkasan profil',
                'content_label' => 'Profil lengkap teknisi',
                'form_intro' => 'Gunakan identitas, pengalaman, foto, dan sertifikasi yang sudah mendapat persetujuan.',
            ],
            'direpair_case' => [
                'label' => 'Kasus Perbaikan',
                'singular' => 'Kasus Perbaikan',
                'rest_base' => 'repair-cases',
                'menu_icon' => 'dashicons-clipboard',
                'title_placeholder' => 'Contoh: Perbaikan TWS yang tidak mengisi daya',
                'summary_label' => 'Ringkasan kasus',
                'content_label' => 'Cerita kasus lengkap',
                'form_intro' => 'Hanya publikasikan kasus nyata yang sudah mendapat izin pelanggan dan tidak memuat data pribadi.',
            ],
            'direpair_price' => [
                'label' => 'Panduan Harga',
                'singular' => 'Panduan Harga',
                'rest_base' => 'pricing-guides',
                'menu_icon' => 'dashicons-money-alt',
                'title_placeholder' => 'Contoh: Estimasi service TWS',
                'summary_label' => 'Ringkasan harga',
                'content_label' => 'Penjelasan harga',
                'form_intro' => 'Gunakan kisaran harga publik. Jangan memasukkan quotation pelanggan atau harga final sebelum diagnosis.',
            ],
            'direpair_faq' => [
                'label' => 'FAQs',
                'singular' => 'FAQ',
                'rest_base' => 'faqs',
                'menu_icon' => 'dashicons-editor-help',
                'title_placeholder' => 'Tulis pertanyaan pelanggan',
                'summary_label' => 'Jawaban singkat',
                'content_label' => 'Jawaban lengkap',
                'form_intro' => 'Gunakan bahasa singkat dan langsung seperti pertanyaan yang benar-benar diajukan pelanggan.',
            ],
            'direpair_warranty' => [
                'label' => 'Garansi',
                'singular' => 'Garansi',
                'rest_base' => 'warranties',
                'menu_icon' => 'dashicons-shield-alt',
                'title_placeholder' => 'Contoh: Garansi pekerjaan service',
                'summary_label' => 'Ringkasan garansi',
                'content_label' => 'Ketentuan garansi lengkap',
                'form_intro' => 'Pastikan durasi, cakupan, dan pengecualian sudah disetujui oleh pihak bisnis.',
            ],
            'direpair_article' => [
                'label' => 'Artikel Panduan',
                'singular' => 'Artikel Panduan',
                'rest_base' => 'knowledge-articles',
                'menu_icon' => 'dashicons-welcome-learn-more',
                'title_placeholder' => 'Judul artikel',
                'summary_label' => 'Ringkasan artikel',
                'content_label' => 'Isi artikel',
                'form_intro' => 'Tulis panduan yang membantu pelanggan memahami perangkat tanpa menggantikan diagnosis teknisi.',
            ],
            'direpair_policy' => [
                'label' => 'Kebijakan',
                'singular' => 'Kebijakan',
                'rest_base' => 'policies',
                'menu_icon' => 'dashicons-privacy',
                'title_placeholder' => 'Contoh: Kebijakan Privasi',
                'summary_label' => 'Ringkasan kebijakan',
                'content_label' => 'Isi kebijakan lengkap',
                'form_intro' => 'Isi kebijakan final yang sudah ditinjau. Hindari menerbitkan teks contoh sebagai dokumen hukum.',
            ],
        ];
    }

    /**
     * @return array<string, array{label: string, singular: string, rest_base: string, post_types: list<string>}>
     */
    public static function taxonomies(): array
    {
        return [
            'direpair_device' => [
                'label' => 'Kategori Perangkat',
                'singular' => 'Kategori Perangkat',
                'rest_base' => 'device-categories',
                'post_types' => ['direpair_service', 'direpair_problem', 'direpair_price', 'direpair_case', 'direpair_article'],
            ],
            'direpair_brand' => [
                'label' => 'Merek',
                'singular' => 'Merek',
                'rest_base' => 'brands',
                'post_types' => ['direpair_service', 'direpair_problem', 'direpair_price', 'direpair_case', 'direpair_article'],
            ],
            'direpair_symptom' => [
                'label' => 'Gejala',
                'singular' => 'Gejala',
                'rest_base' => 'symptoms',
                'post_types' => ['direpair_service', 'direpair_problem', 'direpair_case', 'direpair_article'],
            ],
            'direpair_method' => [
                'label' => 'Metode Layanan',
                'singular' => 'Metode Layanan',
                'rest_base' => 'service-methods',
                'post_types' => ['direpair_service', 'direpair_location', 'direpair_price'],
            ],
            'direpair_city' => [
                'label' => 'Area Layanan',
                'singular' => 'Area Layanan',
                'rest_base' => 'service-areas',
                'post_types' => ['direpair_service', 'direpair_location', 'direpair_case'],
            ],
        ];
    }

    public static function register(): void
    {
        foreach (self::postTypes() as $postType => $definition) {
            register_post_type($postType, [
                'labels' => self::postTypeLabels($definition['label'], $definition['singular']),
                'public' => true,
                'publicly_queryable' => false,
                'exclude_from_search' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'show_in_nav_menus' => false,
                'show_in_rest' => true,
                'rest_base' => $definition['rest_base'],
                'has_archive' => false,
                'rewrite' => false,
                'menu_icon' => $definition['menu_icon'],
                'supports' => [
                    'title',
                    'thumbnail',
                    'revisions',
                ],
            ]);
        }

        foreach (self::taxonomies() as $taxonomy => $definition) {
            register_taxonomy($taxonomy, $definition['post_types'], [
                'labels' => self::taxonomyLabels($definition['label'], $definition['singular']),
                'public' => true,
                'publicly_queryable' => false,
                'show_ui' => true,
                'show_admin_column' => true,
                'show_in_rest' => true,
                'rest_base' => $definition['rest_base'],
                'hierarchical' => true,
                'rewrite' => false,
                'meta_box_cb' => false,
            ]);
        }
    }

    public static function isManaged(string $postType): bool
    {
        return isset(self::postTypes()[$postType]);
    }

    /**
     * @return array<string, string>
     */
    public static function definition(string $postType): ?array
    {
        return self::postTypes()[$postType] ?? null;
    }

    /**
     * @return array<string, array{label: string, singular: string, rest_base: string, post_types: list<string>}>
     */
    public static function taxonomiesForPostType(string $postType): array
    {
        return array_filter(
            self::taxonomies(),
            static fn (array $definition): bool => in_array($postType, $definition['post_types'], true)
        );
    }

    private static function postTypeLabels(string $plural, string $singular): array
    {
        return [
            'name' => $plural,
            'singular_name' => $singular,
            'add_new' => 'Tambah Baru',
            'add_new_item' => sprintf('Tambah %s', $singular),
            'edit_item' => sprintf('Edit %s', $singular),
            'new_item' => sprintf('%s Baru', $singular),
            'view_item' => sprintf('Lihat %s', $singular),
            'search_items' => sprintf('Cari %s', $plural),
            'not_found' => sprintf('%s belum tersedia', $plural),
            'all_items' => sprintf('Semua %s', $plural),
            'item_published' => sprintf('%s berhasil diterbitkan.', $singular),
            'item_updated' => sprintf('%s berhasil diperbarui.', $singular),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function taxonomyLabels(string $plural, string $singular): array
    {
        return [
            'name' => $plural,
            'singular_name' => $singular,
            'search_items' => sprintf('Cari %s', $plural),
            'all_items' => sprintf('Semua %s', $plural),
            'edit_item' => sprintf('Edit %s', $singular),
            'update_item' => sprintf('Perbarui %s', $singular),
            'add_new_item' => sprintf('Tambah %s', $singular),
            'new_item_name' => sprintf('Nama %s Baru', $singular),
            'menu_name' => $plural,
        ];
    }
}
