<?php
/**
 * Plugin Name: Package Unifier
 * Description: A WordPress plugin to unify plugin dependencies into a single global vendor directory.
 * Version: 1.0.0
 * Requires PHP: 8.0
 * Requires at least: 5.6
 * Author: André Cesar Souza de Menezes
 * Author URI: mailto:andre.cs.menezes@gmail.com
 * Tags: dependencies, vendor, composer, WordPress
 * Text Domain: package-unifier
 * Domain Path: /languages
 * Documentation: https://acsmti.com/wordpress/plugins/package-unifier
 * Download Link: https://wordpress.org/plugins/package-unifier
 */

declare(strict_types=1);

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Vendor Verification
|--------------------------------------------------------------------------
|
| This section handles the validation and selection of the appropriate
| vendor autoloader. The process ensures that the global vendor directory
| is used whenever possible. If it is unavailable or incomplete, the local
| vendor directory is used as a fallback, and appropriate admin notices
| are displayed to inform the user.
|
 */

/**
 * Add an admin notice to the WordPress dashboard.
 *
 * @since 1.0.0
 * @param string $message The message to display.
 * @param string $type    The type of notice (success, warning, error, info).
 */
function add_admin_notice(string $message, string $type): void {
    add_action('admin_notices', function () use ($message, $type) {
        printf(
            '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
            esc_attr($type),
            esc_html($message)
        );
    });
}

/**
 * The root directory of the plugin.
 *
 * Defines the absolute path to the plugin's root directory. This constant is used
 * to locate essential files such as composer.json and the vendor directory.
 *
 * @since 1.0.0
 * @var string The absolute path to the plugin's root directory.
 */
const PLUGIN_ROOT_DIR = __DIR__;

/**
 * The global vendor directory path.
 *
 * Defines the absolute path to the shared vendor directory in the WordPress root.
 * This directory is used to consolidate dependencies shared across plugins.
 *
 * @since 1.0.0
 * @var string The absolute path to the global vendor directory.
 */
const GLOBAL_VENDOR_DIR = ABSPATH . 'vendor';

/**
 * The global autoload file.
 *
 * Points to the `autoload.php` file within the global vendor directory.
 * This file is used to autoload dependencies shared across plugins.
 *
 * @since 1.0.0
 * @var string The absolute path to the global autoload file.
 */
const GLOBAL_AUTOLOAD = GLOBAL_VENDOR_DIR . '/autoload.php';

/**
 * The local autoload file.
 *
 * Points to the `autoload.php` file within this plugin's vendor directory.
 * This file is used to autoload dependencies specific to this plugin.
 *
 * @since 1.0.0
 * @var string The absolute path to the local autoload file.
 */
const LOCAL_AUTOLOAD = PLUGIN_ROOT_DIR . '/vendor/autoload.php';

/**
 * The composer.lock file path.
 *
 * Points to the `composer.lock` file, which lists all dependencies and their versions
 * required by this plugin.
 *
 * @since 1.0.0
 * @var string The absolute path to the composer.lock file.
 */
const COMPOSER_LOCK_PATH = PLUGIN_ROOT_DIR . '/composer.lock';

/**
 * The text domain for translations.
 *
 * This constant is used for internationalization (i18n) and localization (l10n),
 * enabling the plugin to be translated into other languages.
 *
 * @since 1.0.0
 * @var string The text domain used for translations.
 */
const PLUGIN_TEXT_DOMAIN = 'package-unifier';

/*
 * Initialize the selected autoload path.
 *
 * This variable will hold the path to the autoload file (either global or local)
 * after all vendor validation steps are completed.
 *
 * @since 1.0.0
 * @var string|null The path to the autoload file or null if none is available.
 */
$autoloadPath = null;

/**
 * Step 1: Verify the existence of composer.json, composer.lock, and vendor directory.
 */
if (
        !file_exists(COMPOSER_LOCK_PATH)
    ||  !file_exists(PLUGIN_ROOT_DIR . '/composer.json')
    ||  !file_exists(PLUGIN_ROOT_DIR . '/vendor')
) {
    deactivate_plugins(plugin_basename(__FILE__));
    wp_die(
        __('Composer files or vendor directory missing. Please ensure dependencies are properly installed.', PLUGIN_TEXT_DOMAIN),
        __('Plugin Activation Error', PLUGIN_TEXT_DOMAIN),
        ['back_link' => true]
    );
}

/**
 * Step 2: Validate the composer.lock file as valid JSON.
 */
$composerData   = file_get_contents(COMPOSER_LOCK_PATH);
$lockContent    = json_decode($composerData, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    deactivate_plugins(plugin_basename(__FILE__));
    wp_die(
        __('Invalid composer.lock file. Please ensure it is a valid JSON file.', PLUGIN_TEXT_DOMAIN),
        __('Plugin Activation Error', PLUGIN_TEXT_DOMAIN),
        ['back_link' => true]
    );
}

/**
 * Step 3: Check if the global vendor directory exists. If not, use the local vendor.
 */
if (!file_exists(GLOBAL_AUTOLOAD)) {
    $autoloadPath = LOCAL_AUTOLOAD;
    add_admin_notice(
        __('Global vendor directory not found. Using local vendor.', PLUGIN_TEXT_DOMAIN),
        'warning'
    );
}

/**
 * Step 4: If global vendor exists, validate dependencies listed in composer.lock.
 */
if (!$autoloadPath) {
    $requiredPackages = array_column($lockContent['packages'], 'name');
    $allPackagesExist = true;

    foreach ($requiredPackages as $package) {
        if (!is_dir(GLOBAL_VENDOR_DIR . '/' . $package)) {
            $allPackagesExist = false;

            add_admin_notice(
                __('Global vendor autoload failed. Falling back to local vendor.', PLUGIN_TEXT_DOMAIN),
                'error'
            );

            break;
        }
    }

    $autoloadPath = $allPackagesExist ? GLOBAL_AUTOLOAD : LOCAL_AUTOLOAD;
}

/**
 * Step 5: Final fallback if no valid vendor is available.
 */
if (!$autoloadPath || !file_exists($autoloadPath)) {
    deactivate_plugins(plugin_basename(__FILE__));
    wp_die(
        __('No valid vendor autoload file found. Please install dependencies.', PLUGIN_TEXT_DOMAIN),
        __('Plugin Activation Error', PLUGIN_TEXT_DOMAIN),
        ['back_link' => true]
    );
}

/*
|--------------------------------------------------------------------------
| Cleanup
|--------------------------------------------------------------------------
|
| This section ensures that all declared constants, variables, and functions
| are removed from the global scope to prevent conflicts with other plugins
| or WordPress itself.
|
*/

// Remove all defined constants.
if (defined('PLUGIN_ROOT_DIR')) {
    unset($GLOBALS['PLUGIN_ROOT_DIR']);
}
if (defined('GLOBAL_VENDOR_DIR')) {
    unset($GLOBALS['GLOBAL_VENDOR_DIR']);
}
if (defined('GLOBAL_AUTOLOAD')) {
    unset($GLOBALS['GLOBAL_AUTOLOAD']);
}
if (defined('LOCAL_AUTOLOAD')) {
    unset($GLOBALS['LOCAL_AUTOLOAD']);
}
if (defined('COMPOSER_LOCK_PATH')) {
    unset($GLOBALS['COMPOSER_LOCK_PATH']);
}
if (defined('PLUGIN_TEXT_DOMAIN')) {
    unset($GLOBALS['PLUGIN_TEXT_DOMAIN']);
}

// Unset defined functions.
if (function_exists('add_admin_notice')) {
    unset($GLOBALS['add_admin_notice']);
}

/*
|--------------------------------------------------------------------------
| Init Application
|--------------------------------------------------------------------------
|
| This section is responsible for initializing the core functionality of
| the plugin. It verifies the PHP version, registers plugin hooks, and
| initializes the plugin's primary classes.
|
*/

// Autoload dependencies using Composer.
require_once $autoloadPath;

use PackageUnifier\Shared\Config;
use PackageUnifier\Infrastructure\WordPress\PluginActivator;

/**
 * Verify PHP version compatibility.
 *
 * This plugin requires PHP 8.0 or higher. If the current PHP version is lower,
 * deactivate the plugin and display an admin notice.
 */
if (version_compare(PHP_VERSION, Config::REQUIRED_PHP_VERSION, '<')) {
    deactivate_plugins(Config::BASENAME);
    wp_die(
        sprintf(
        /* translators: %s: Current PHP version */
            __('%s %s requires PHP %s or higher. Your current version is %s.', Config::TEXT_DOMAIN),
            Config::PLUGIN_NAME,
            Config::VERSION,
            Config::REQUIRED_PHP_VERSION,
            PHP_VERSION
        ),
        __('Plugin Activation Error', Config::TEXT_DOMAIN),
        ['back_link' => true]
    );
}

/**
 * Verify the existence of the Composer autoload file.
 *
 * This ensures that all required dependencies are available. If the autoload
 * file is missing, the plugin cannot function and will be deactivated.
 */
if (!file_exists(Config::ROOT_DIR . 'vendor/autoload.php')) {
    deactivate_plugins(Config::BASENAME);
    wp_die(
        __('Autoload file not found. Please install the dependencies via Composer before activating the plugin.', Config::TEXT_DOMAIN),
        __('Plugin Activation Error', Config::TEXT_DOMAIN),
        ['back_link' => true]
    );
}

/**
 * Register plugin activation and deactivation hooks.
 *
 * - Activation Hook: Prepares the environment by creating necessary directories.
 * - Deactivation Hook: Placeholder for future cleanup logic.
 */
register_activation_hook(Config::BASENAME, [PluginActivator::class, 'activate']);
register_deactivation_hook(Config::BASENAME, [PluginActivator::class, 'deactivate']);

/**
 * Initialize the plugin.
 *
 * This method sets up all required WordPress hooks and actions needed
 * for the plugin's operation. Delegates hook registration to the
 * `PluginActivator` class for better separation of concerns.
 */
PluginActivator::init();
