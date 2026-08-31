<?php

declare(strict_types=1);

namespace Direpair\Content;

final class Publishing
{
    private const CRON_HOOK = 'direpair_dispatch_content_webhook';
    private const PENDING_OPTION = 'direpair_publish_pending_event';
    private const LAST_RESULT_OPTION = 'direpair_publish_last_result';

    private static bool $guardRunning = false;

    public static function register(): void
    {
        add_action('save_post', [self::class, 'schedulePublishWebhook'], 100, 3);
        add_action('save_post', [self::class, 'enforceProductionGuard'], 110, 3);
        add_action('transition_post_status', [self::class, 'scheduleStatusChangeWebhook'], 100, 3);
        add_action('before_delete_post', [self::class, 'scheduleDeletedContentWebhook'], 10, 2);
        add_action('created_term', [self::class, 'scheduleTaxonomyWebhook'], 10, 3);
        add_action('edited_term', [self::class, 'scheduleTaxonomyWebhook'], 10, 3);
        add_action('delete_term', [self::class, 'scheduleTaxonomyWebhook'], 10, 3);
        add_action('update_option_'.Settings::PUBLIC_OPTION, [self::class, 'scheduleSettingsWebhook'], 10, 3);
        add_action(self::CRON_HOOK, [self::class, 'dispatchWebhook']);

        foreach (array_keys(ContentTypes::postTypes()) as $postType) {
            add_filter('rest_pre_insert_'.$postType, [self::class, 'guardRestPublish'], 10, 2);
        }
    }

    public static function schedulePublishWebhook(int $postId, \WP_Post $post, bool $update): void
    {
        if (! ContentTypes::isManaged($post->post_type) || $post->post_status !== 'publish' || wp_is_post_revision($postId)) {
            return;
        }

        self::schedule(
            $update ? 'content.updated' : 'content.published',
            $post->post_type,
            $postId,
            $post->post_name
        );
    }

    public static function scheduleDeletedContentWebhook(int $postId, \WP_Post $post): void
    {
        if (! ContentTypes::isManaged($post->post_type) || $post->post_status !== 'publish') {
            return;
        }

        self::schedule('content.deleted', $post->post_type, $postId, $post->post_name);
    }

    public static function scheduleStatusChangeWebhook(string $newStatus, string $oldStatus, \WP_Post $post): void
    {
        if (! ContentTypes::isManaged($post->post_type) || $oldStatus !== 'publish' || $newStatus === 'publish') {
            return;
        }

        self::schedule('content.unpublished', $post->post_type, $post->ID, $post->post_name);
    }

    public static function scheduleTaxonomyWebhook(int $termId, int $taxonomyTermId, string $taxonomy): void
    {
        unset($taxonomyTermId);

        if (! isset(ContentTypes::taxonomies()[$taxonomy])) {
            return;
        }

        self::schedule('taxonomy.updated', $taxonomy, $termId, '');
    }

    public static function scheduleSettingsWebhook(mixed $oldValue, mixed $newValue, string $option): void
    {
        unset($oldValue, $newValue, $option);

        self::schedule('settings.updated', 'site-settings', 0, '');
    }

    private static function schedule(string $event, string $objectType, int $objectId, string $slug): void
    {
        update_option(self::PENDING_OPTION, [
            'event' => $event,
            'object_type' => $objectType,
            'object_id' => $objectId,
            'slug' => $slug,
            'modified_at' => gmdate(DATE_ATOM),
        ], false);

        update_option(self::LAST_RESULT_OPTION, [
            'state' => 'queued',
            'message' => 'Perubahan tersimpan dan menunggu build Vercel.',
            'updated_at' => gmdate(DATE_ATOM),
        ], false);

        if (wp_next_scheduled(self::CRON_HOOK) === false) {
            wp_schedule_single_event(time() + 30, self::CRON_HOOK);
        }
    }

    public static function dispatchWebhook(): void
    {
        $url = (string) get_option(Settings::WEBHOOK_URL_OPTION, '');
        $secret = (string) get_option(Settings::WEBHOOK_SECRET_OPTION, '');

        if ($url === '') {
            self::recordResult('idle', 'Deploy Hook Vercel belum diisi. Konten tetap tersimpan di WordPress.');

            return;
        }

        $timestamp = (string) time();
        $pending = get_option(self::PENDING_OPTION, []);
        $body = wp_json_encode(is_array($pending) ? $pending : []);

        if (! is_string($body)) {
            self::recordResult('error', 'Payload perubahan tidak dapat dibuat. Coba simpan ulang konten.');

            return;
        }

        $headers = ['Content-Type' => 'application/json'];

        if ($secret !== '') {
            $headers['X-Direpair-Timestamp'] = $timestamp;
            $headers['X-Direpair-Signature'] = hash_hmac('sha256', $timestamp.'.'.$body, $secret);
        }

        $response = wp_safe_remote_post($url, [
            'timeout' => 10,
            'redirection' => 0,
            'sslverify' => true,
            'headers' => $headers,
            'body' => $body,
        ]);

        if (is_wp_error($response)) {
            error_log('Direpair content webhook failed: '.$response->get_error_code());
            self::recordResult('error', 'Vercel belum dapat dihubungi: '.$response->get_error_code());

            return;
        }

        $statusCode = wp_remote_retrieve_response_code($response);

        if ($statusCode < 200 || $statusCode >= 300) {
            self::recordResult('error', sprintf('Vercel menolak Deploy Hook dengan HTTP %d.', $statusCode));

            return;
        }

        delete_option(self::PENDING_OPTION);
        self::recordResult('success', 'Build Vercel berhasil diminta. Website akan diperbarui setelah deployment selesai.');
    }

    /**
     * @return array{state: string, message: string, updated_at: string}
     */
    public static function lastResult(): array
    {
        $result = get_option(self::LAST_RESULT_OPTION, []);

        return [
            'state' => is_array($result) ? (string) ($result['state'] ?? 'idle') : 'idle',
            'message' => is_array($result) ? (string) ($result['message'] ?? 'Belum ada permintaan build.') : 'Belum ada permintaan build.',
            'updated_at' => is_array($result) ? (string) ($result['updated_at'] ?? '') : '',
        ];
    }

    private static function recordResult(string $state, string $message): void
    {
        update_option(self::LAST_RESULT_OPTION, [
            'state' => $state,
            'message' => $message,
            'updated_at' => gmdate(DATE_ATOM),
        ], false);
    }

    public static function enforceProductionGuard(int $postId, \WP_Post $post): void
    {
        if (self::$guardRunning || wp_get_environment_type() !== 'production') {
            return;
        }

        if (! ContentTypes::isManaged($post->post_type) || $post->post_status !== 'publish') {
            return;
        }

        $isDemo = (bool) get_post_meta($postId, MetaFields::key('is_demo'), true);

        if (! $isDemo) {
            return;
        }

        self::$guardRunning = true;
        wp_update_post([
            'ID' => $postId,
            'post_status' => 'draft',
        ]);
        update_post_meta($postId, MetaFields::key('noindex'), true);
        self::$guardRunning = false;
    }

    public static function guardRestPublish(mixed $preparedPost, \WP_REST_Request $request): mixed
    {
        if (wp_get_environment_type() !== 'production') {
            return $preparedPost;
        }

        $status = (string) $request->get_param('status');
        $meta = $request->get_param('meta');
        $isDemo = is_array($meta) && ! empty($meta[MetaFields::key('is_demo')]);

        if ($status === 'publish' && $isDemo) {
            return new \WP_Error(
                'direpair_demo_publish_blocked',
                'Demo content cannot be published in production.',
                ['status' => 400]
            );
        }

        return $preparedPost;
    }
}
