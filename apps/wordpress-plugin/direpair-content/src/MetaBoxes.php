<?php

declare(strict_types=1);

namespace Direpair\Content;

final class MetaBoxes
{
    private const NONCE_ACTION = 'direpair_content_meta';
    private const NONCE_NAME = 'direpair_content_nonce';

    private const SECTION_DEFINITIONS = [
        'publication' => [
            'label' => 'Kesiapan tayang',
            'description' => 'Pastikan informasi sudah benar sebelum konten diterbitkan.',
        ],
        'presentation' => [
            'label' => 'Tampilan halaman',
            'description' => 'Atur teks pembuka dan tombol tanpa mengubah layout website.',
        ],
        'details' => [
            'label' => 'Detail konten',
            'description' => 'Isi fakta dan detail yang khusus untuk jenis konten ini.',
        ],
        'pricing' => [
            'label' => 'Harga, waktu, dan garansi',
            'description' => 'Gunakan nilai publik yang sudah disetujui. Harga final tetap mengikuti diagnosis.',
        ],
        'relations' => [
            'label' => 'Konten terkait',
            'description' => 'Hubungkan konten ini dengan layanan, masalah, teknisi, atau FAQ lain.',
        ],
        'seo' => [
            'label' => 'Pengaturan Google',
            'description' => 'Opsional. Buka bagian ini hanya bila perlu mengubah tampilan hasil pencarian.',
        ],
    ];

    private static bool $savingCoreFields = false;

    public static function register(): void
    {
        foreach (array_keys(ContentTypes::postTypes()) as $postType) {
            add_meta_box(
                'direpair-content-fields',
                'Direpair Content Fields',
                [self::class, 'render'],
                $postType,
                'normal',
                'high'
            );
        }
    }

    public static function render(\WP_Post $post): void
    {
        wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);

        $postTypeDefinition = ContentTypes::definition($post->post_type);

        if ($postTypeDefinition === null) {
            return;
        }

        echo '<div class="direpair-structured-form">';
        printf(
            '<header class="direpair-editor-intro"><span>FORMULIR KONTEN / DIREPAIR</span><h2>%s</h2><p>%s</p></header>',
            esc_html($postTypeDefinition['singular']),
            esc_html($postTypeDefinition['form_intro'])
        );

        self::renderCoreFields($post, $postTypeDefinition);
        self::renderTaxonomyFields($post);

        $groupedFields = [];

        foreach (MetaFields::forPostType($post->post_type) as $name => $definition) {
            if ($name === 'external_uuid') {
                continue;
            }

            $groupedFields[self::sectionForField($name)][$name] = $definition;
        }

        foreach (array_keys(self::SECTION_DEFINITIONS) as $sectionKey) {
            $fields = $groupedFields[$sectionKey] ?? [];

            if ($fields === []) {
                continue;
            }

            self::renderSection($post, $sectionKey, $fields);
        }

        echo '</div>';
    }

    public static function save(int $postId, \WP_Post $post): void
    {
        if (self::$savingCoreFields || ! ContentTypes::isManaged($post->post_type)) {
            return;
        }

        if (wp_is_post_revision($postId) || wp_is_post_autosave($postId)) {
            return;
        }

        $nonce = isset($_POST[self::NONCE_NAME])
            ? sanitize_text_field(wp_unslash($_POST[self::NONCE_NAME]))
            : '';

        if ($nonce === '' || ! wp_verify_nonce($nonce, self::NONCE_ACTION)) {
            return;
        }

        if (! current_user_can('edit_post', $postId)) {
            return;
        }

        $submitted = isset($_POST['direpair_meta']) && is_array($_POST['direpair_meta'])
            ? wp_unslash($_POST['direpair_meta'])
            : [];

        $coreSubmitted = isset($_POST['direpair_core']) && is_array($_POST['direpair_core'])
            ? wp_unslash($_POST['direpair_core'])
            : [];

        $taxonomySubmitted = isset($_POST['direpair_taxonomies']) && is_array($_POST['direpair_taxonomies'])
            ? wp_unslash($_POST['direpair_taxonomies'])
            : [];

        self::saveCoreFields($postId, $post, $coreSubmitted);
        self::saveTaxonomies($postId, $post->post_type, $taxonomySubmitted);

        foreach (MetaFields::forPostType($post->post_type) as $name => $definition) {
            if ($name === 'external_uuid') {
                continue;
            }

            $value = self::sanitizeSubmittedValue($submitted[$name] ?? null, $definition);

            if ($value === null) {
                delete_post_meta($postId, MetaFields::key($name));
            } else {
                update_post_meta($postId, MetaFields::key($name), $value);
            }

            if (($definition['control'] ?? '') === 'taxonomy_checklist' && isset($definition['taxonomy'])) {
                wp_set_object_terms($postId, is_array($value) ? $value : [], $definition['taxonomy']);
            }
        }

        delete_post_meta($postId, MetaFields::key('service_methods'));

        self::ensureExternalUuid($postId);
    }

    public static function ensureExternalUuid(int $postId): void
    {
        if (wp_is_post_revision($postId) || wp_is_post_autosave($postId)) {
            return;
        }

        $postType = get_post_type($postId);

        if (! is_string($postType) || ! ContentTypes::isManaged($postType)) {
            return;
        }

        $key = MetaFields::key('external_uuid');

        if ((string) get_post_meta($postId, $key, true) === '') {
            update_post_meta($postId, $key, wp_generate_uuid4());
        }
    }

    /**
     * @param array{summary_label: string, content_label: string} $postTypeDefinition
     */
    private static function renderCoreFields(\WP_Post $post, array $postTypeDefinition): void
    {
        echo '<section class="direpair-form-section direpair-form-section--core">';
        echo '<div class="direpair-section-heading"><h3>Isi utama</h3><p>Judul diisi pada kolom paling atas. Gunakan ringkasan untuk kartu daftar dan isi lengkap untuk halaman detail.</p></div>';
        echo '<div class="direpair-content-fields">';
        echo '<div class="direpair-field direpair-field--wide">';
        printf('<label for="direpair-core-excerpt">%s</label>', esc_html($postTypeDefinition['summary_label']));
        printf(
            '<textarea id="direpair-core-excerpt" name="direpair_core[excerpt]" rows="3" class="widefat" placeholder="Tulis ringkasan singkat yang mudah dipahami.">%s</textarea>',
            esc_textarea($post->post_excerpt)
        );
        echo '<p class="description">Disarankan satu sampai dua kalimat. Hindari klaim yang belum dapat dibuktikan.</p>';
        echo '</div>';
        echo '<div class="direpair-field direpair-field--wide direpair-field--editor">';
        printf('<label for="direpair_core_content">%s</label>', esc_html($postTypeDefinition['content_label']));
        wp_editor($post->post_content, 'direpair_core_content', [
            'textarea_name' => 'direpair_core[content]',
            'textarea_rows' => 10,
            'media_buttons' => false,
            'teeny' => true,
            'quicktags' => false,
            'tinymce' => [
                'toolbar1' => 'formatselect,bold,italic,bullist,numlist,link,unlink,undo,redo',
                'toolbar2' => '',
            ],
        ]);
        echo '<p class="description">Gunakan heading, paragraf, daftar, dan tautan seperlunya. Layout halaman tetap diatur otomatis oleh website.</p>';
        echo '</div></div></section>';
    }

    private static function renderTaxonomyFields(\WP_Post $post): void
    {
        $taxonomies = ContentTypes::taxonomiesForPostType($post->post_type);

        if ($taxonomies === []) {
            return;
        }

        echo '<section class="direpair-form-section">';
        echo '<div class="direpair-section-heading"><h3>Klasifikasi dan cakupan</h3><p>Pilih kategori yang membantu pelanggan menemukan konten ini. Semua pilihan berada di formulir utama, bukan panel samping.</p></div>';
        echo '<div class="direpair-content-fields">';

        foreach ($taxonomies as $taxonomy => $definition) {
            $selected = wp_get_object_terms($post->ID, $taxonomy, ['fields' => 'ids']);
            $selected = is_wp_error($selected) ? [] : $selected;
            $fieldId = 'direpair-taxonomy-'.sanitize_html_class($taxonomy);
            $fieldName = sprintf('direpair_taxonomies[%s]', $taxonomy);

            echo '<div class="direpair-field direpair-field--wide">';
            printf('<span class="direpair-field-label">%s</span>', esc_html($definition['label']));
            self::renderTaxonomyChecklist($fieldId, $fieldName, $selected, $taxonomy);
            echo '</div>';
        }

        echo '</div></section>';
    }

    /**
     * @param array<string, array<string, mixed>> $fields
     */
    private static function renderSection(\WP_Post $post, string $sectionKey, array $fields): void
    {
        $section = self::SECTION_DEFINITIONS[$sectionKey];
        $isCollapsible = $sectionKey === 'seo';

        if ($isCollapsible) {
            echo '<details class="direpair-form-section direpair-form-section--details">';
            printf('<summary><strong>%s</strong><span>%s</span></summary>', esc_html($section['label']), esc_html($section['description']));
        } else {
            echo '<section class="direpair-form-section">';
            printf(
                '<div class="direpair-section-heading"><h3>%s</h3><p>%s</p></div>',
                esc_html($section['label']),
                esc_html($section['description'])
            );
        }

        echo '<div class="direpair-content-fields">';

        foreach ($fields as $name => $definition) {
            self::renderField($post, $name, $definition);
        }

        echo '</div>';
        echo $isCollapsible ? '</details>' : '</section>';
    }

    /**
     * @param array{label: string, type: string, control?: string, description?: string, default?: mixed, placeholder?: string, relation_post_type?: string, taxonomy?: string} $definition
     */
    private static function renderField(\WP_Post $post, string $name, array $definition): void
    {
        $key = MetaFields::key($name);
        $value = metadata_exists('post', $post->ID, $key)
            ? get_post_meta($post->ID, $key, true)
            : ($definition['default'] ?? '');
        $control = $definition['control'] ?? 'text';
        $fieldId = 'direpair-meta-'.sanitize_html_class($name);
        $fieldName = sprintf('direpair_meta[%s]', $name);
        $wideClass = in_array($control, ['textarea', 'textarea_list', 'post_select', 'taxonomy_checklist'], true)
            ? ' direpair-field--wide'
            : '';

        printf('<div class="direpair-field%s">', esc_attr($wideClass));
        printf('<label for="%s">%s</label>', esc_attr($fieldId), esc_html($definition['label']));

        if ($control === 'checkbox') {
            printf('<input type="hidden" name="%s" value="0">', esc_attr($fieldName));
            printf(
                '<label class="direpair-check"><input id="%s" type="checkbox" name="%s" value="1" %s><span>Ya, aktifkan pilihan ini</span></label>',
                esc_attr($fieldId),
                esc_attr($fieldName),
                checked((bool) $value, true, false)
            );
        } elseif ($control === 'post_select') {
            self::renderPostSelect($post, $fieldId, $fieldName, $value, (string) ($definition['relation_post_type'] ?? ''));
        } elseif ($control === 'taxonomy_checklist') {
            self::renderTaxonomyChecklist($fieldId, $fieldName, $value, (string) ($definition['taxonomy'] ?? ''));
        } elseif ($control === 'textarea' || $control === 'textarea_list') {
            $displayValue = is_array($value) ? implode("\n", $value) : (string) $value;
            printf(
                '<textarea id="%s" name="%s" rows="4" class="widefat">%s</textarea>',
                esc_attr($fieldId),
                esc_attr($fieldName),
                esc_textarea($displayValue)
            );
        } else {
            $displayValue = is_array($value) ? implode(',', $value) : (string) $value;
            $inputType = match ($control) {
                'url' => 'url',
                'date' => 'date',
                'readonly' => 'text',
                default => in_array($definition['type'], ['integer', 'number'], true) ? 'number' : 'text',
            };
            $step = $definition['type'] === 'number' ? ' step="0.01"' : '';
            $readonly = $control === 'readonly' ? ' readonly' : '';
            $placeholder = isset($definition['placeholder'])
                ? sprintf(' placeholder="%s"', esc_attr($definition['placeholder']))
                : '';
            $minimum = in_array($definition['type'], ['integer', 'number'], true) ? ' min="0"' : '';
            printf(
                '<input id="%s" type="%s" name="%s" value="%s" class="widefat"%s%s%s%s>',
                esc_attr($fieldId),
                esc_attr($inputType),
                esc_attr($fieldName),
                esc_attr($displayValue),
                $step,
                $readonly,
                $placeholder,
                $minimum
            );
        }

        if (isset($definition['description'])) {
            printf('<p class="description">%s</p>', esc_html($definition['description']));
        }

        echo '</div>';
    }

    private static function renderPostSelect(\WP_Post $post, string $fieldId, string $fieldName, mixed $value, string $postType): void
    {
        $selected = is_array($value) ? array_map('intval', $value) : [];
        $options = $postType === '' ? [] : get_posts([
            'post_type' => $postType,
            'post_status' => ['publish', 'draft', 'pending', 'private'],
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        if ($options === []) {
            printf('<select id="%s" class="widefat" disabled><option>Belum ada pilihan yang tersedia</option></select>', esc_attr($fieldId));

            return;
        }

        printf('<select id="%s" name="%s[]" class="widefat direpair-multi-select" multiple size="%d">', esc_attr($fieldId), esc_attr($fieldName), min(8, max(4, count($options))));

        foreach ($options as $option) {
            if (! $option instanceof \WP_Post || $option->ID === $post->ID) {
                continue;
            }

            printf(
                '<option value="%d" %s>%s%s</option>',
                $option->ID,
                selected(in_array($option->ID, $selected, true), true, false),
                esc_html(get_the_title($option)),
                $option->post_status === 'publish' ? '' : esc_html(' — '.$option->post_status)
            );
        }

        echo '</select><p class="description">Tahan Ctrl (Windows) atau Command (Mac) untuk memilih lebih dari satu.</p>';
    }

    private static function renderTaxonomyChecklist(string $fieldId, string $fieldName, mixed $value, string $taxonomy): void
    {
        $selected = is_array($value) ? array_map('strval', $value) : [];
        $terms = $taxonomy === '' ? [] : get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
        ]);

        if (is_wp_error($terms) || $terms === []) {
            echo '<p class="direpair-empty-field">Pilihan belum tersedia. Tambahkan pilihan dari menu klasifikasi di sebelah kiri.</p>';

            return;
        }

        printf('<div id="%s" class="direpair-choice-grid">', esc_attr($fieldId));

        foreach ($terms as $term) {
            if (! $term instanceof \WP_Term) {
                continue;
            }

            printf(
                '<label><input type="checkbox" name="%s[]" value="%s" %s><span>%s</span></label>',
                esc_attr($fieldName),
                esc_attr((string) $term->term_id),
                checked(in_array((string) $term->term_id, $selected, true), true, false),
                esc_html($term->name)
            );
        }

        echo '</div>';
    }

    /**
     * @param array<string, mixed> $submitted
     */
    private static function saveCoreFields(int $postId, \WP_Post $post, array $submitted): void
    {
        $excerpt = sanitize_textarea_field((string) ($submitted['excerpt'] ?? ''));
        $content = wp_kses_post((string) ($submitted['content'] ?? ''));

        if ($excerpt === $post->post_excerpt && $content === $post->post_content) {
            return;
        }

        self::$savingCoreFields = true;
        wp_update_post([
            'ID' => $postId,
            'post_excerpt' => $excerpt,
            'post_content' => $content,
        ]);
        self::$savingCoreFields = false;
    }

    /**
     * @param array<string, mixed> $submitted
     */
    private static function saveTaxonomies(int $postId, string $postType, array $submitted): void
    {
        foreach (ContentTypes::taxonomiesForPostType($postType) as $taxonomy => $_definition) {
            $terms = isset($submitted[$taxonomy]) && is_array($submitted[$taxonomy])
                ? MetaFields::sanitizeIntegerArray($submitted[$taxonomy])
                : [];

            wp_set_object_terms($postId, $terms, $taxonomy);
        }
    }

    /**
     * @param array{type: string, control?: string} $definition
     */
    private static function sanitizeSubmittedValue(mixed $value, array $definition): mixed
    {
        $type = $definition['type'];
        $control = $definition['control'] ?? 'text';

        if (in_array($type, ['integer', 'number'], true) && ($value === null || $value === '')) {
            return null;
        }

        return match ($type) {
            'boolean' => MetaFields::sanitizeBoolean($value),
            'integer' => absint($value),
            'number' => MetaFields::sanitizeNumber($value),
            'array_integer' => MetaFields::sanitizeIntegerArray(
                is_array($value) ? $value : (preg_split('/\s*,\s*/', (string) $value) ?: [])
            ),
            'array_string' => MetaFields::sanitizeStringArray(
                is_array($value) ? $value : (preg_split('/\R/', (string) $value) ?: [])
            ),
            default => match ($control) {
                'url' => esc_url_raw((string) $value),
                'textarea' => sanitize_textarea_field((string) $value),
                default => sanitize_text_field((string) $value),
            },
        };
    }

    private static function sectionForField(string $name): string
    {
        return match (true) {
            in_array($name, ['is_demo', 'is_verified', 'publish_consent'], true) => 'publication',
            in_array($name, ['hero_eyebrow', 'hero_title', 'hero_summary', 'primary_cta_label', 'primary_cta_url', 'quick_answer'], true) => 'presentation',
            in_array($name, ['price_from', 'price_to', 'diagnosis_fee', 'currency', 'turnaround_min_days', 'turnaround_max_days', 'pricing_disclaimer', 'warranty_summary', 'warranty_duration_days', 'warranty_exclusions'], true) => 'pricing',
            in_array($name, ['related_problem_ids', 'related_service_ids', 'related_technician_ids', 'faq_ids'], true) => 'relations',
            in_array($name, ['noindex', 'seo_title', 'seo_description'], true) => 'seo',
            default => 'details',
        };
    }
}
