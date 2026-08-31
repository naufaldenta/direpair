<?php

declare(strict_types=1);

namespace Direpair\Content;

final class Settings
{
    public const PUBLIC_OPTION = 'direpair_site_settings';
    public const WEBHOOK_URL_OPTION = 'direpair_publish_webhook_url';
    public const WEBHOOK_SECRET_OPTION = 'direpair_publish_webhook_secret';

    private const GROUP = 'direpair_content_settings';

    /**
     * @return array<string, array{label: string, type: string, default: string|bool, description?: string}>
     */
    public static function publicFields(): array
    {
        return [
            'business_name' => ['label' => 'Nama bisnis', 'type' => 'text', 'default' => 'Direpair'],
            'tagline' => ['label' => 'Tagline', 'type' => 'text', 'default' => 'Kami Memperbaiki yang Sulit Diperbaiki.'],
            'phone' => ['label' => 'Nomor telepon utama', 'type' => 'text', 'default' => ''],
            'whatsapp' => ['label' => 'Nomor WhatsApp utama', 'type' => 'text', 'default' => ''],
            'email' => ['label' => 'Email utama', 'type' => 'email', 'default' => ''],
            'street_address' => ['label' => 'Alamat lengkap', 'type' => 'text', 'default' => ''],
            'city' => ['label' => 'Kota', 'type' => 'text', 'default' => ''],
            'region' => ['label' => 'Provinsi', 'type' => 'text', 'default' => ''],
            'postal_code' => ['label' => 'Kode pos', 'type' => 'text', 'default' => ''],
            'country_code' => ['label' => 'Kode negara', 'type' => 'text', 'default' => 'ID'],
            'opening_hours_summary' => ['label' => 'Ringkasan jam operasional', 'type' => 'text', 'default' => ''],
            'booking_path' => ['label' => 'Alamat halaman booking', 'type' => 'text', 'default' => '/booking/'],
            'status_path' => ['label' => 'Alamat halaman cek status', 'type' => 'text', 'default' => '/cek-status/'],
            'instagram_url' => ['label' => 'Instagram URL', 'type' => 'url', 'default' => ''],
            'youtube_url' => ['label' => 'YouTube URL', 'type' => 'url', 'default' => ''],
            'facebook_url' => ['label' => 'Facebook URL', 'type' => 'url', 'default' => ''],
            'default_pricing_disclaimer' => [
                'label' => 'Catatan harga bawaan',
                'type' => 'textarea',
                'default' => 'Harga final diberikan setelah diagnosis dan sebelum pekerjaan dimulai.',
            ],
            'demo_mode' => [
                'label' => 'Mode demo',
                'type' => 'checkbox',
                'default' => true,
                'description' => 'Aktifkan hanya untuk development/staging. Jangan gunakan konten demo sebagai klaim bisnis.',
            ],
        ];
    }

    public static function register(): void
    {
        register_setting(self::GROUP, self::PUBLIC_OPTION, [
            'type' => 'object',
            'default' => self::defaults(),
            'sanitize_callback' => [self::class, 'sanitizePublicSettings'],
            'show_in_rest' => [
                'schema' => [
                    'type' => 'object',
                    'properties' => self::restProperties(),
                ],
            ],
        ]);

        register_setting(self::GROUP, self::WEBHOOK_URL_OPTION, [
            'type' => 'string',
            'default' => '',
            'sanitize_callback' => 'esc_url_raw',
            'show_in_rest' => false,
        ]);

        register_setting(self::GROUP, self::WEBHOOK_SECRET_OPTION, [
            'type' => 'string',
            'default' => '',
            'sanitize_callback' => [self::class, 'sanitizeWebhookSecret'],
            'show_in_rest' => false,
        ]);
    }

    public static function addMenu(): void
    {
        add_options_page(
            'Direpair Content Settings',
            'Direpair Content',
            'manage_options',
            'direpair-content',
            [self::class, 'renderPage']
        );
    }

    public static function renderPage(): void
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        $settings = self::getPublic();
        $webhookUrl = (string) get_option(self::WEBHOOK_URL_OPTION, '');
        $publishResult = Publishing::lastResult();

        echo '<div class="wrap direpair-settings"><header class="direpair-settings-head"><span class="route-code">CMS / PUBLIC CONTROL</span><h1>Direpair Content</h1><p>Kelola informasi publik, jalur booking, kanal kontak, dan pemicu publish tanpa menyentuh data operasional pelanggan.</p></header>';
        echo '<div class="direpair-settings-route" aria-hidden="true"><span></span></div>';
        echo '<form class="direpair-settings-form" method="post" action="options.php">';
        settings_fields(self::GROUP);
        echo '<h2 class="direpair-settings-section">Informasi website publik</h2>';
        echo '<table class="form-table" role="presentation">';

        foreach (self::publicFields() as $name => $definition) {
            self::renderField($name, $definition, $settings[$name] ?? $definition['default']);
        }

        echo '</table>';
        echo '<h2 class="direpair-settings-section">Pembaruan otomatis ke Vercel</h2>';
        printf(
            '<div class="direpair-deploy-status" data-state="%s"><strong>Status terakhir</strong><p>%s</p>%s</div>',
            esc_attr($publishResult['state']),
            esc_html($publishResult['message']),
            $publishResult['updated_at'] === '' ? '' : sprintf('<time datetime="%s">%s UTC</time>', esc_attr($publishResult['updated_at']), esc_html($publishResult['updated_at']))
        );
        echo '<table class="form-table" role="presentation">';
        self::renderPrivateField(
            'Vercel Deploy Hook URL',
            self::WEBHOOK_URL_OPTION,
            $webhookUrl,
            'url',
            'Salin dari Vercel → Project Settings → Git → Deploy Hooks. URL ini bersifat rahasia.'
        );
        echo '</table>';
        submit_button();
        echo '</form></div>';
    }

    /**
     * @return array<string, string|bool>
     */
    public static function getPublic(): array
    {
        $value = get_option(self::PUBLIC_OPTION, []);

        return array_merge(self::defaults(), is_array($value) ? $value : []);
    }

    /**
     * @param mixed $value
     * @return array<string, string|bool>
     */
    public static function sanitizePublicSettings(mixed $value): array
    {
        $value = is_array($value) ? $value : [];
        $sanitized = [];

        foreach (self::publicFields() as $name => $definition) {
            $submitted = $value[$name] ?? $definition['default'];
            $sanitized[$name] = match ($definition['type']) {
                'checkbox' => filter_var($submitted, FILTER_VALIDATE_BOOLEAN),
                'email' => sanitize_email((string) $submitted),
                'url' => esc_url_raw((string) $submitted),
                'textarea' => sanitize_textarea_field((string) $submitted),
                default => sanitize_text_field((string) $submitted),
            };
        }

        return $sanitized;
    }

    public static function sanitizeWebhookSecret(mixed $value): string
    {
        $sanitized = sanitize_text_field((string) $value);

        if ($sanitized === '') {
            return (string) get_option(self::WEBHOOK_SECRET_OPTION, '');
        }

        return $sanitized;
    }

    /**
     * @return array<string, string|bool>
     */
    public static function defaults(): array
    {
        $defaults = [];

        foreach (self::publicFields() as $name => $definition) {
            $defaults[$name] = $definition['default'];
        }

        return $defaults;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function restProperties(): array
    {
        $properties = [];

        foreach (self::publicFields() as $name => $definition) {
            $properties[$name] = [
                'type' => $definition['type'] === 'checkbox' ? 'boolean' : 'string',
                'default' => $definition['default'],
            ];
        }

        return $properties;
    }

    /**
     * @param array{label: string, type: string, description?: string} $definition
     */
    private static function renderField(string $name, array $definition, string|bool $value): void
    {
        $optionName = sprintf('%s[%s]', self::PUBLIC_OPTION, $name);
        printf('<tr><th scope="row"><label for="%s">%s</label></th><td>', esc_attr($name), esc_html($definition['label']));

        if ($definition['type'] === 'checkbox') {
            printf('<input type="hidden" name="%s" value="0">', esc_attr($optionName));
            printf(
                '<label><input id="%s" type="checkbox" name="%s" value="1" %s> Enabled</label>',
                esc_attr($name),
                esc_attr($optionName),
                checked((bool) $value, true, false)
            );
        } elseif ($definition['type'] === 'textarea') {
            printf(
                '<textarea id="%s" name="%s" rows="4" class="large-text">%s</textarea>',
                esc_attr($name),
                esc_attr($optionName),
                esc_textarea((string) $value)
            );
        } else {
            printf(
                '<input id="%s" type="%s" name="%s" value="%s" class="regular-text">',
                esc_attr($name),
                esc_attr($definition['type']),
                esc_attr($optionName),
                esc_attr((string) $value)
            );
        }

        if (isset($definition['description'])) {
            printf('<p class="description">%s</p>', esc_html($definition['description']));
        }

        echo '</td></tr>';
    }

    private static function renderPrivateField(string $label, string $name, string $value, string $type, string $description = ''): void
    {
        printf('<tr><th scope="row"><label for="%s">%s</label></th><td>', esc_attr($name), esc_html($label));
        printf(
            '<input id="%s" type="%s" name="%s" value="%s" class="regular-text" autocomplete="off">',
            esc_attr($name),
            esc_attr($type),
            esc_attr($name),
            esc_attr($value)
        );
        if ($description !== '') {
            printf('<p class="description">%s</p>', esc_html($description));
        }
        echo '</td></tr>';
    }
}
