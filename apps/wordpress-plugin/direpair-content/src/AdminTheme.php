<?php

declare(strict_types=1);

namespace Direpair\Content;

final class AdminTheme
{
    public static function enqueue(): void
    {
        wp_enqueue_style(
            'direpair-admin-theme',
            plugins_url('assets/admin.css', DIREPAIR_CONTENT_FILE),
            [],
            DIREPAIR_CONTENT_VERSION
        );
    }

    public static function enqueueLogin(): void
    {
        self::enqueue();
    }

    public static function bodyClass(string $classes): string
    {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        $structuredEditor = $screen instanceof \WP_Screen && ContentTypes::isManaged((string) $screen->post_type)
            ? ' direpair-structured-editor'
            : '';

        return trim($classes.' direpair-admin'.$structuredEditor);
    }

    /**
     * @param list<string> $classes
     * @return list<string>
     */
    public static function loginBodyClass(array $classes): array
    {
        $classes[] = 'direpair-admin';

        return $classes;
    }

    public static function useBlockEditor(bool $useBlockEditor, string $postType): bool
    {
        return ContentTypes::isManaged($postType) ? false : $useBlockEditor;
    }

    public static function titlePlaceholder(string $placeholder, \WP_Post $post): string
    {
        $definition = ContentTypes::definition($post->post_type);

        return $definition === null ? $placeholder : $definition['title_placeholder'];
    }

    public static function simplifyMenu(): void
    {
        remove_menu_page('edit.php');
        remove_menu_page('edit.php?post_type=page');
        remove_menu_page('edit-comments.php');
    }

    public static function renderFavicon(): void
    {
        printf(
            '<link rel="icon" type="image/svg+xml" href="%s">',
            esc_url(plugins_url('assets/favicon.svg', DIREPAIR_CONTENT_FILE))
        );
    }
}
