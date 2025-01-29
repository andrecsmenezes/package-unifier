<?php

namespace PackageUnifier\Application;

use PackageUnifier\Domain\Plugin;
use PackageUnifier\Infrastructure\ComposerService;

/**
 * Scans all plugins for vendor folders and consolidates dependencies.
 *
 * This class is responsible for scanning all installed plugins in the WordPress
 * `wp-content/plugins` directory. It detects vendor folders, analyzes their
 * dependencies, and consolidates them into a global `vendor` directory at the root
 * of the WordPress installation.
 *
 * @package PackageUnifier\Application
 * @since 1.0.0
 */
class VendorScanner {
    /**
     * Initiates the scanning process for all plugins.
     *
     * This method iterates over all plugins in the WordPress plugins directory,
     * checks for the presence of a `vendor` folder, and processes its dependencies
     * using the `ComposerService`.
     *
     * @since 1.0.0
     *
     * @uses self::listPlugins() To retrieve the list of plugin directories.
     * @uses self::processPlugin() To process individual plugins for dependencies.
     * @link https://www.php.net/manual/en/function.glob.php For listing directories.
     *
     * @return void
     */
    public function scan(): void {
        $plugins = $this->listPlugins();

        foreach ($plugins as $pluginPath) {
            $plugin = new Plugin($pluginPath);
            if ($plugin->hasVendor()) {
                $this->processPlugin($plugin);
            }
        }
    }

    /**
     * Lists all active plugins in the WordPress plugins directory.
     *
     * This method retrieves all directories in the `wp-content/plugins` folder
     * using PHP's `glob` function. It ensures only valid plugin directories are
     * included in the results.
     *
     * @since 1.0.0
     *
     * @global string $WP_CONTENT_DIR The absolute path to the `wp-content` directory.
     * @uses glob() To list directories.
     * @link https://www.php.net/manual/en/function.glob.php Documentation for `glob`.
     *
     * @return array<string> A list of plugin directory paths.
     */
    private function listPlugins(): array {
        return glob(WP_CONTENT_DIR . '/plugins/*', GLOB_ONLYDIR) ?? [];
    }

    /**
     * Processes each plugin for dependencies.
     *
     * This method analyzes a plugin's `vendor` folder and determines whether it
     * contains a `composer.json` file. If present, it delegates dependency management
     * to the `ComposerService` to install or update packages in the global vendor
     * directory. Otherwise, it moves the existing `vendor` packages to the global
     * directory.
     *
     * @since 1.0.0
     *
     * @uses Plugin::hasComposerJson() To check for a `composer.json` file.
     * @uses Plugin::getComposerJsonPath() To retrieve the path of `composer.json`.
     * @uses Plugin::getVendorPath() To retrieve the path of the plugin's vendor folder.
     * @uses ComposerService::installDependencies() To install dependencies from `composer.json`.
     * @uses ComposerService::movePackages() To move packages to the global vendor directory.
     *
     * @param Plugin $plugin The plugin instance being processed.
     *
     * @return void
     */
    private function processPlugin(Plugin $plugin): void {
        $composerService = new ComposerService();

        if ($plugin->hasComposerJson()) {
            $composerService->installDependencies($plugin->getComposerJsonPath());
        } else {
            $composerService->movePackages($plugin->getVendorPath());
        }
    }
}
