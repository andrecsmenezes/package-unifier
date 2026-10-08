<?php
/**
 * Plugin Name: Package Unifier
 * Description: Experimental Composer dependency unification research; runtime consolidation is disabled.
 * Version: 1.0.0
 * Requires PHP: 8.0
 * Requires at least: 5.6
 * Text Domain: package-unifier
 * Domain Path: /languages
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// Only the plugin-local, version-locked autoloader is safe before compatibility review.
$localAutoload = __DIR__ . '/vendor/autoload.php';
if (!is_file($localAutoload)) {
    add_action('admin_notices', static function (): void {
        echo '<div class="notice notice-error"><p>'
            . esc_html__(
                'Package Unifier is inactive: install its local Composer dependencies before use.',
                'package-unifier'
            )
            . '</p></div>';
    });
    return;
}

require_once $localAutoload;

register_activation_hook(
    __FILE__,
    [\PackageUnifier\Infrastructure\WordPress\PluginActivator::class, 'activate']
);
register_deactivation_hook(
    __FILE__,
    [\PackageUnifier\Infrastructure\WordPress\PluginActivator::class, 'deactivate']
);
\PackageUnifier\Infrastructure\WordPress\PluginActivator::init();
