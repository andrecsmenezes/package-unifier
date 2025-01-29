<?php

namespace PackageUnifier\Infrastructure\WordPress;

use PackageUnifier\Application\VendorScanner;

/**
 * Handles the registration of WordPress hooks.
 *
 * This class manages all WordPress-specific hooks used by the plugin,
 * ensuring the integration of core functionality such as scanning
 * for vendor folders and loading translations for internationalization.
 *
 * @package PackageUnifier\Infrastructure\WordPress
 * @since 1.0.0
 */
class Hooks {
    /**
     * Registers WordPress hooks for the plugin.
     *
     * Hooks included:
     * - `init`: Used to trigger the scanning process of plugins for vendor folders.
     * - `plugins_loaded`: Ensures the plugin's textdomain is loaded for translations.
     *
     * @since 1.0.0
     *
     * @uses add_action() Registers the actions with WordPress.
     * @link https://developer.wordpress.org/reference/functions/add_action/ Documentation for `add_action`.
     * @return void
     */
    public static function register(): void {
        add_action('init', [self::class, 'scanPlugins']);
        add_action('plugins_loaded', [self::class, 'loadTextDomain']);
    }

    /**
     * Scans all plugins for vendor folders and consolidates dependencies.
     *
     * This method is triggered by the `init` hook. It instantiates the
     * `VendorScanner` class, which handles the actual scanning and
     * consolidation of vendor folders into a global directory.
     *
     * @since 1.0.0
     *
     * @uses VendorScanner::scan() Performs the scanning of plugins.
     * @link https://developer.wordpress.org/reference/hooks/init/ Documentation for the `init` hook.
     * @return void
     */
    public static function scanPlugins(): void {
        (new VendorScanner())->scan();
    }

    /**
     * Loads the plugin's textdomain for internationalization.
     *
     * This method ensures that translations for the plugin are properly loaded
     * by WordPress. It is triggered by the `plugins_loaded` hook, which is
     * recommended for localization setup.
     *
     * @since 1.0.0
     *
     * @uses load_plugin_textdomain() Loads the textdomain for translations.
     * @link https://developer.wordpress.org/reference/functions/load_plugin_textdomain/ Documentation for `load_plugin_textdomain`.
     * @link https://developer.wordpress.org/plugins/internationalization/ WordPress plugin internationalization guide.
     * @return void
     */
    public static function loadTextDomain(): void {
        load_plugin_textdomain(
            domain: 'package-unifier', // The plugin's unique text domain.
            plugin_rel_path: dirname(plugin_basename(__FILE__)) . '/languages' // Relative path to language files.
        );
    }
}
