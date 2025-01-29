<?php

namespace PackageUnifier\Infrastructure\WordPress;

use RuntimeException;

/**
 * Handles plugin activation, deactivation, and initialization.
 *
 * This class ensures that the plugin integrates seamlessly with WordPress
 * by handling critical lifecycle events such as activation, deactivation,
 * and initialization. It also prepares the environment for the plugin's
 * operation, such as creating required directories and registering hooks.
 *
 * @package PackageUnifier\Infrastructure\WordPress
 * @since 1.0.0
 */
class PluginActivator {
    /**
     * Actions to perform upon plugin activation.
     *
     * - Ensures the global vendor directory exists in the root of the WordPress installation.
     * - Prepares the environment for the plugin's operation by creating necessary directories.
     * - Logs any errors related to directory creation for debugging purposes.
     *
     * @since 1.0.0
     *
     * @global string $ABSPATH The absolute path to the WordPress root directory.
     * @uses ABSPATH The global constant that holds the WordPress installation path.
     * @link https://developer.wordpress.org/reference/functions/register_activation_hook/ Documentation for activation hooks.
     *
     * @throws RuntimeException If the directory cannot be created.
     * @return void
     */
    public static function activate(): void {
        // Ensure the global vendor directory exists.
        if (!is_dir(ABSPATH . 'vendor')) {
            if (!mkdir(ABSPATH . 'vendor', 0755, true) && !is_dir(ABSPATH . 'vendor')) {
                // Log an error if the directory could not be created.
                throw new RuntimeException(sprintf(
                    'Failed to create global vendor directory at %s',
                    ABSPATH . 'vendor'
                ));
            }
        }
    }

    /**
     * Actions to perform upon plugin deactivation.
     *
     * This method can be used to clean up resources or revert changes made during activation.
     * Examples of deactivation tasks include:
     * - Removing temporary data.
     * - Unregistering scheduled tasks.
     * - Reverting changes made to global settings.
     *
     * @since 1.0.0
     *
     * @uses register_deactivation_hook() Registers the deactivation hook.
     * @link https://developer.wordpress.org/reference/functions/register_deactivation_hook/ Documentation for deactivation hooks.
     *
     * @todo Add cleanup logic for future versions.
     * @return void
     */
    public static function deactivate(): void {
        // Placeholder for deactivation logic.
    }

    /**
     * Initializes the plugin by registering necessary hooks.
     *
     * This method sets up all required WordPress hooks and actions needed
     * for the plugin's operation. It delegates the actual registration
     * of hooks to the `Hooks` class, maintaining a clean separation of concerns.
     *
     * Example of hooks registered:
     * - `init`: For initializing plugin-specific logic.
     * - `plugins_loaded`: For loading translations or localization files.
     *
     * @since 1.0.0
     *
     * @uses Hooks::register() Registers hooks for the plugin.
     * @link https://developer.wordpress.org/plugins/hooks/ Documentation for WordPress hooks.
     *
     * @return void
     */
    public static function init(): void {
        Hooks::register(); // Registers WordPress hooks via the Hooks class.
    }
}
