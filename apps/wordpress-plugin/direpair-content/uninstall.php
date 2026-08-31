<?php

declare(strict_types=1);

if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

if (! defined('DIREPAIR_REMOVE_DATA') || DIREPAIR_REMOVE_DATA !== true) {
    return;
}

delete_option('direpair_site_settings');
delete_option('direpair_publish_webhook_url');
delete_option('direpair_publish_webhook_secret');

