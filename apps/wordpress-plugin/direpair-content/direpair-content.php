<?php
/**
 * Plugin Name: Direpair Content
 * Description: Structured public content and REST API for the Direpair Astro frontend.
 * Version: 0.2.0
 * Requires at least: 6.6
 * Requires PHP: 8.2
 * Author: Direpair
 * Text Domain: direpair-content
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

define('DIREPAIR_CONTENT_VERSION', '0.2.0');
define('DIREPAIR_CONTENT_FILE', __FILE__);
define('DIREPAIR_CONTENT_DIR', plugin_dir_path(__FILE__));

$direpairContentFiles = [
    'ContentTypes.php',
    'MetaFields.php',
    'MetaBoxes.php',
    'AdminTheme.php',
    'Settings.php',
    'RestApi.php',
    'Publishing.php',
    'DemoSeeder.php',
    'Plugin.php',
];

foreach ($direpairContentFiles as $direpairContentFile) {
    require_once DIREPAIR_CONTENT_DIR.'src/'.$direpairContentFile;
}

Direpair\Content\Plugin::boot();
