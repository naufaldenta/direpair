<?php

declare(strict_types=1);

namespace Direpair\Content;

final class Plugin
{
    public static function boot(): void
    {
        add_action('init', [ContentTypes::class, 'register'], 5);
        add_action('init', [MetaFields::class, 'register'], 10);
        add_action('init', [DemoSeeder::class, 'registerCli'], 20);
        add_action('add_meta_boxes', [MetaBoxes::class, 'register']);
        add_action('admin_enqueue_scripts', [AdminTheme::class, 'enqueue']);
        add_action('login_enqueue_scripts', [AdminTheme::class, 'enqueueLogin']);
        add_action('admin_head', [AdminTheme::class, 'renderFavicon']);
        add_action('login_head', [AdminTheme::class, 'renderFavicon']);
        add_action('wp_head', [AdminTheme::class, 'renderFavicon']);
        add_filter('admin_body_class', [AdminTheme::class, 'bodyClass']);
        add_filter('login_body_class', [AdminTheme::class, 'loginBodyClass']);
        add_filter('use_block_editor_for_post_type', [AdminTheme::class, 'useBlockEditor'], 20, 2);
        add_filter('enter_title_here', [AdminTheme::class, 'titlePlaceholder'], 20, 2);
        add_action('admin_menu', [AdminTheme::class, 'simplifyMenu'], 999);
        add_action('save_post', [MetaBoxes::class, 'ensureExternalUuid'], 5);
        add_action('save_post', [MetaBoxes::class, 'save'], 10, 2);
        add_action('admin_init', [Settings::class, 'register']);
        add_action('admin_menu', [Settings::class, 'addMenu']);
        add_action('rest_api_init', [Settings::class, 'register']);
        add_action('rest_api_init', [RestApi::class, 'register']);

        Publishing::register();

        register_activation_hook(DIREPAIR_CONTENT_FILE, [self::class, 'activate']);
    }

    public static function activate(): void
    {
        ContentTypes::register();
        MetaFields::register();
        flush_rewrite_rules();
    }
}
