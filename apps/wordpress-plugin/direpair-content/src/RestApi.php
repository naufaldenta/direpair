<?php

declare(strict_types=1);

namespace Direpair\Content;

final class RestApi
{
    private const NAMESPACE = 'direpair/v1';

    /**
     * @return array<string, string>
     */
    private static function routes(): array
    {
        return [
            'services' => 'direpair_service',
            'problems' => 'direpair_problem',
            'locations' => 'direpair_location',
            'technicians' => 'direpair_tech',
            'repair-cases' => 'direpair_case',
            'pricing-guides' => 'direpair_price',
            'faqs' => 'direpair_faq',
            'warranties' => 'direpair_warranty',
            'knowledge-articles' => 'direpair_article',
            'policies' => 'direpair_policy',
        ];
    }

    public static function register(): void
    {
        register_rest_route(self::NAMESPACE, '/health', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [self::class, 'health'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route(self::NAMESPACE, '/site-settings', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [self::class, 'siteSettings'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route(self::NAMESPACE, '/catalog', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [self::class, 'catalog'],
            'permission_callback' => '__return_true',
        ]);

        foreach (self::routes() as $route => $postType) {
            register_rest_route(self::NAMESPACE, '/'.$route, [
                'methods' => \WP_REST_Server::READABLE,
                'callback' => static fn (\WP_REST_Request $request): \WP_REST_Response => self::collection($request, $postType),
                'permission_callback' => '__return_true',
                'args' => self::collectionArgs(),
            ]);

            register_rest_route(self::NAMESPACE, '/'.$route.'/(?P<slug>[a-z0-9-]+)', [
                'methods' => \WP_REST_Server::READABLE,
                'callback' => static fn (\WP_REST_Request $request): \WP_REST_Response|\WP_Error => self::single($request, $postType),
                'permission_callback' => '__return_true',
                'args' => [
                    'slug' => [
                        'required' => true,
                        'sanitize_callback' => 'sanitize_title',
                    ],
                ],
            ]);
        }
    }

    public static function health(): \WP_REST_Response
    {
        return new \WP_REST_Response([
            'status' => 'ok',
            'service' => 'direpair-content',
            'version' => DIREPAIR_CONTENT_VERSION,
            'environment' => wp_get_environment_type(),
        ]);
    }

    public static function siteSettings(): \WP_REST_Response
    {
        return new \WP_REST_Response([
            'data' => Settings::getPublic(),
            'meta' => [
                'version' => DIREPAIR_CONTENT_VERSION,
                'environment' => wp_get_environment_type(),
            ],
        ]);
    }

    public static function catalog(): \WP_REST_Response
    {
        $taxonomies = [];

        foreach (ContentTypes::taxonomies() as $taxonomy => $definition) {
            $terms = get_terms([
                'taxonomy' => $taxonomy,
                'hide_empty' => false,
            ]);

            $taxonomies[$definition['rest_base']] = is_wp_error($terms)
                ? []
                : array_map(
                    static fn (\WP_Term $term): array => [
                        'id' => $term->term_id,
                        'slug' => $term->slug,
                        'name' => self::plainText($term->name),
                        'description' => self::plainText($term->description),
                    ],
                    $terms
                );
        }

        $services = get_posts([
            'post_type' => 'direpair_service',
            'post_status' => 'publish',
            'posts_per_page' => 100,
            'orderby' => 'menu_order title',
            'order' => 'ASC',
            'meta_query' => self::visibilityMetaQuery('direpair_service'),
        ]);

        return new \WP_REST_Response([
            'data' => [
                'services' => array_map(
                    static fn (\WP_Post $post): array => [
                        'id' => $post->ID,
                        'uuid' => (string) get_post_meta($post->ID, MetaFields::key('external_uuid'), true),
                        'slug' => $post->post_name,
                        'name' => self::plainText(get_the_title($post)),
                    ],
                    $services
                ),
                'taxonomies' => $taxonomies,
            ],
        ]);
    }

    private static function collection(\WP_REST_Request $request, string $postType): \WP_REST_Response
    {
        $page = max(1, (int) $request->get_param('page'));
        $perPage = min(100, max(1, (int) $request->get_param('per_page')));
        $query = new \WP_Query([
            'post_type' => $postType,
            'post_status' => 'publish',
            'posts_per_page' => $perPage,
            'paged' => $page,
            'orderby' => 'date',
            'order' => 'DESC',
            'meta_query' => self::visibilityMetaQuery($postType),
        ]);

        return new \WP_REST_Response([
            'data' => array_map([self::class, 'serializePost'], $query->posts),
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => (int) $query->found_posts,
                'total_pages' => (int) $query->max_num_pages,
            ],
        ]);
    }

    private static function single(\WP_REST_Request $request, string $postType): \WP_REST_Response|\WP_Error
    {
        $posts = get_posts([
            'post_type' => $postType,
            'post_status' => 'publish',
            'name' => (string) $request->get_param('slug'),
            'posts_per_page' => 1,
            'meta_query' => self::visibilityMetaQuery($postType),
        ]);

        if ($posts === []) {
            return new \WP_Error('direpair_not_found', 'Content not found.', ['status' => 404]);
        }

        return new \WP_REST_Response(['data' => self::serializePost($posts[0])]);
    }

    /**
     * @return array<string, mixed>
     */
    private static function serializePost(\WP_Post $post): array
    {
        $meta = [];

        foreach (MetaFields::forPostType($post->post_type) as $name => $definition) {
            $key = MetaFields::key($name);
            $meta[$name] = metadata_exists('post', $post->ID, $key)
                ? get_post_meta($post->ID, $key, true)
                : ($definition['default'] ?? null);
        }

        $terms = [];

        foreach (get_object_taxonomies($post->post_type) as $taxonomy) {
            if (! str_starts_with($taxonomy, 'direpair_')) {
                continue;
            }

            $postTerms = get_the_terms($post, $taxonomy);
            $terms[$taxonomy] = is_array($postTerms)
                ? array_map(
                    static fn (\WP_Term $term): array => [
                        'id' => $term->term_id,
                        'slug' => $term->slug,
                        'name' => self::plainText($term->name),
                    ],
                    $postTerms
                )
                : [];
        }

        return [
            'id' => $post->ID,
            'uuid' => (string) ($meta['external_uuid'] ?? ''),
            'type' => $post->post_type,
            'slug' => $post->post_name,
            'title' => self::plainText(get_the_title($post)),
            'excerpt' => self::plainText(get_the_excerpt($post)),
            'content' => wp_kses_post(apply_filters('the_content', $post->post_content)),
            'featured_image' => self::featuredImage($post->ID),
            'meta' => $meta,
            'terms' => $terms,
            'published_at' => get_post_time(DATE_ATOM, true, $post),
            'modified_at' => get_post_modified_time(DATE_ATOM, true, $post),
        ];
    }

    private static function plainText(string $value): string
    {
        return html_entity_decode(wp_strip_all_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function featuredImage(int $postId): ?array
    {
        $attachmentId = get_post_thumbnail_id($postId);

        if ($attachmentId === 0) {
            return null;
        }

        $source = wp_get_attachment_image_src($attachmentId, 'full');

        if (! is_array($source)) {
            return null;
        }

        return [
            'id' => $attachmentId,
            'url' => $source[0],
            'width' => (int) $source[1],
            'height' => (int) $source[2],
            'alt' => (string) get_post_meta($attachmentId, '_wp_attachment_image_alt', true),
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function collectionArgs(): array
    {
        return [
            'page' => [
                'default' => 1,
                'sanitize_callback' => 'absint',
                'validate_callback' => static fn (mixed $value): bool => is_numeric($value) && (int) $value >= 1,
            ],
            'per_page' => [
                'default' => 20,
                'sanitize_callback' => 'absint',
                'validate_callback' => static fn (mixed $value): bool => is_numeric($value) && (int) $value >= 1 && (int) $value <= 100,
            ],
        ];
    }

    /**
     * @return array<int|string, mixed>
     */
    private static function visibilityMetaQuery(string $postType): array
    {
        if (wp_get_environment_type() !== 'production') {
            return [];
        }

        $query = [
            'relation' => 'AND',
            [
                'relation' => 'OR',
                [
                    'key' => MetaFields::key('is_demo'),
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key' => MetaFields::key('is_demo'),
                    'value' => '1',
                    'compare' => '!=',
                ],
            ],
            [
                'key' => MetaFields::key('is_verified'),
                'value' => '1',
                'compare' => '=',
            ],
        ];

        if ($postType === 'direpair_case') {
            $query[] = [
                'key' => MetaFields::key('publish_consent'),
                'value' => '1',
                'compare' => '=',
            ];
        }

        return $query;
    }
}
