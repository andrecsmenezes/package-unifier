<?php

namespace PackageUnifier\Infrastructure;

use RuntimeException;

/**
 * Handles Composer-related operations for dependency management.
 *
 * This class provides methods to manage dependencies for plugins, including
 * installing or updating dependencies using Composer and moving vendor packages
 * from plugin directories to a global vendor directory.
 *
 * @package PackageUnifier\Infrastructure
 * @since 1.0.0
 */
class ComposerService {
    /**
     * The path to the global vendor directory.
     *
     * This property stores the absolute path to the `vendor` directory located
     * at the root of the WordPress installation.
     *
     * @since 1.0.0
     * @global string $ABSPATH The absolute path to the WordPress root directory.
     * @var string
     */
    private string $globalVendorDir;

    /**
     * Initializes the ComposerService with the global vendor directory path.
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->globalVendorDir = ABSPATH . 'vendor';
    }

    /**
     * Installs or updates dependencies in the global vendor directory.
     *
     * This method uses Composer's CLI command to install dependencies defined
     * in a `composer.json` file located in a plugin's vendor folder.
     *
     * If the installation fails, an error is logged for debugging purposes.
     *
     * @since 1.0.0
     *
     * @param string $composerJsonPath The absolute path to the `composer.json` file to process.
     * @uses escapeshellarg() To sanitize CLI arguments.
     * @uses exec() To execute the Composer CLI command.
     * @link https://getcomposer.org/doc/03-cli.md#require Documentation for Composer's `require` command.
     * @throws RuntimeException If the Composer command fails.
     * @return void
     */
    public function installDependencies(string $composerJsonPath): void {
        $cmd = sprintf(
            'composer require --working-dir=%s %s',
            escapeshellarg($this->globalVendorDir),
            escapeshellarg($composerJsonPath)
        );
        exec($cmd, $output, $returnVar);

        if ($returnVar !== 0) {
            error_log("Error installing dependencies for $composerJsonPath");
            throw new RuntimeException(
                sprintf('Failed to install dependencies for %s', $composerJsonPath)
            );
        }
    }

    /**
     * Moves packages from a plugin's vendor folder to the global vendor directory.
     *
     * This method scans the specified `vendorPath` for package directories
     * and uses `installDependencies` to consolidate them into the global directory.
     *
     * @since 1.0.0
     *
     * @param string $vendorPath The absolute path to the vendor directory of a plugin.
     * @uses glob() To list package directories in the vendor path.
     * @uses self::installDependencies() To handle the consolidation process.
     * @link https://www.php.net/manual/en/function.glob.php Documentation for `glob`.
     * @return void
     */
    public function movePackages(string $vendorPath): void {
        $packages = glob($vendorPath . '/*', GLOB_ONLYDIR) ?? [];

        foreach ($packages as $package) {
            try {
                $this->installDependencies($package);
            } catch (RuntimeException $e) {
                error_log("Failed to move package $package: " . $e->getMessage());
            }
        }
    }
}
