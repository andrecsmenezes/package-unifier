<?php

namespace PackageUnifier\Application;

use RuntimeException;

/**
 * Handles the update of Composer's autoloader to reflect consolidated dependencies.
 *
 * This class ensures that the autoloader in the global `vendor` directory is
 * regenerated whenever new dependencies are added or moved from individual plugins.
 *
 * @package PackageUnifier\Application
 * @since 1.0.0
 */
class AutoloaderUpdater {
    /**
     * The path to the global vendor directory.
     *
     * This property stores the absolute path to the `vendor` directory,
     * where Composer's autoloader is consolidated.
     *
     * @since 1.0.0
     * @var string $globalVendorDir The absolute path to the global vendor directory.
     */
    private string $globalVendorDir;

    /**
     * Initializes the AutoloaderUpdater with the global vendor directory path.
     *
     * @since 1.0.0
     *
     * @param string $globalVendorDir The path to the global vendor directory.
     */
    public function __construct(string $globalVendorDir) {
        $this->globalVendorDir = $globalVendorDir;
    }

    /**
     * Updates the Composer autoloader by running the `composer dump-autoload` command.
     *
     * This method regenerates the Composer autoloader in the global `vendor`
     * directory, ensuring that all consolidated dependencies are properly indexed
     * and available for use by plugins.
     *
     * @since 1.0.0
     *
     * @uses exec() To run the `composer dump-autoload` command.
     * @link https://getcomposer.org/doc/03-cli.md#dump-autoload Documentation for Composer's `dump-autoload`.
     *
     * @throws RuntimeException If the Composer command fails.
     *
     * @return void
     */
    public function updateAutoloader(): void {
        $cmd = sprintf('composer dump-autoload --working-dir=%s', escapeshellarg($this->globalVendorDir));
        exec($cmd, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new RuntimeException('Failed to update the Composer autoloader.');
        }
    }
}
