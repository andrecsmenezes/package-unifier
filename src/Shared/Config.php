<?php

namespace PackageUnifier\Shared;

/**
 * Provides shared configuration values for the plugin.
 *
 * This class centralizes configuration constants and utility methods
 * that are used across the plugin, such as paths, default settings,
 * and metadata. It ensures all shared data is easily accessible
 * and maintains a single source of truth.
 *
 * @package PackageUnifier\Shared
 * @since 1.0.0
 */
class Config {
    /**
     * The name of the plugin.
     *
     * This constant defines the name of the plugin, which can be displayed
     * in admin notices, logs, or other parts of the WordPress admin panel.
     *
     * Example:
     * - Displaying the plugin name in error messages or notices.
     *
     * @since 1.0.0
     * @var string
     */
    public const PLUGIN_NAME = 'Package Unifier';

    /**
     * The current version of the plugin.
     *
     * This constant represents the version of the plugin and is used
     * in various parts of the plugin for version-specific operations,
     * such as enqueueing assets with versioned URLs.
     *
     * @since 1.0.0
     * @var string
     */
    public const VERSION = '1.0.0';

    /**
     * The minimum required PHP version.
     *
     * This constant defines the minimum PHP version required for
     * the plugin to function. It is used during activation to ensure
     * compatibility with the server environment.
     *
     * @since 1.0.0
     * @var string
     */
    public const REQUIRED_PHP_VERSION = '8.0';

    /**
     * The root directory of the plugin.
     *
     * This constant defines the absolute path to the root directory
     * of the plugin. It is used to reference files and directories
     * within the plugin's structure.
     *
     * @since 1.0.0
     * @var string
     */
    public const ROOT_DIR = __DIR__ . '/../';

    /**
     * The basename of the plugin file.
     *
     * This constant defines the basename of the main plugin file,
     * which is used for registering activation and deactivation hooks.
     *
     * @since 1.0.0
     * @var string
     */
    public const BASENAME = 'package-unifier/package-unifier.php';

    /**
     * The text domain for the plugin.
     *
     * This constant defines the text domain used for internationalization
     * and localization of the plugin. It is used with WordPress functions
     * like `__()` and `_e()` to load the appropriate translation strings.
     *
     * @since 1.0.0
     * @var string
     */
    public const TEXT_DOMAIN = 'package-unifier';

    /**
     * The path to the global vendor directory.
     *
     * This constant defines the absolute path to the global `vendor` directory,
     * located in the root of the WordPress installation. The global directory
     * is used to consolidate dependencies across multiple plugins.
     *
     * @since 1.0.0
     *
     * @global string $ABSPATH The absolute path to the WordPress root directory.
     * @uses ABSPATH The global constant for the WordPress root directory path.
     * @link https://developer.wordpress.org/reference/functions/ABSPATH/ Documentation for ABSPATH.
     * @var string
     */
    public const GLOBAL_VENDOR_DIR = ABSPATH . 'vendor';

    /**
     * The path to the global Composer autoload file.
     *
     * This constant defines the absolute path to the `autoload.php` file
     * located in the global `vendor` directory. It ensures that shared
     * dependencies across plugins are properly loaded.
     *
     * @since 1.0.0
     *
     * @global string $ABSPATH The absolute path to the WordPress root directory.
     * @uses ABSPATH The global constant for the WordPress root directory path.
     * @link https://getcomposer.org/doc/01-basic-usage.md#autoloading Documentation for Composer autoloading.
     * @var string
     */
    public const GLOBAL_VENDOR_AUTOLOAD = self::GLOBAL_VENDOR_DIR . '/autoload.php';

    /**
     * The path to the plugin-specific Composer autoload file.
     *
     * This constant defines the absolute path to the `autoload.php` file
     * located in the plugin's `vendor` directory. It ensures that the
     * plugin's own dependencies are loaded if needed.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public const VENDOR_AUTOLOAD = self::ROOT_DIR . 'vendor/autoload.php';


    /**
     * The relative path for language files.
     *
     * This constant defines the path to the `languages` folder, which
     * is used for storing translation files for the plugin. The path
     * is relative to the plugin's root directory.
     *
     * @since 1.0.0
     * @var string
     */
    public const LANGUAGES_PATH = '/languages';
}
