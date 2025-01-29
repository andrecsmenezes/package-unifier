<?php

namespace PackageUnifier\Application;

use RuntimeException;

/**
 * Handles the installation of Composer dependencies.
 *
 * This class facilitates the installation of dependencies for plugins by
 * utilizing the Composer CLI. It ensures that all necessary packages are
 * consolidated in the global vendor directory, improving consistency
 * and reducing redundancy across plugins.
 *
 * @package PackageUnifier\Application
 * @since 1.0.0
 */
class DependencyInstaller {
    /**
     * The path to the global vendor directory.
     *
     * This property stores the absolute path to the global `vendor` directory,
     * where all dependencies are consolidated.
     *
     * @since 1.0.0
     *
     * @var string $globalVendorDir The absolute path to the global vendor directory.
     */
    private string $globalVendorDir;

    /**
     * Initializes the DependencyInstaller with the global vendor directory path.
     *
     * @param string $globalVendorDir The path to the global vendor directory.
     * @since 1.0.0
     */
    public function __construct(string $globalVendorDir) {
        $this->globalVendorDir = $globalVendorDir;
    }

    /**
     * Installs dependencies from a given `composer.json` file.
     *
     * This method uses Composer's `require` command to install the dependencies
     * specified in the provided `composer.json` file. It ensures that all required
     * packages are properly installed in the global vendor directory.
     *
     * @since 1.0.0
     *
     * @param string $composerJsonPath The absolute path to the `composer.json` file to be processed.
     * @uses exec() To run the Composer CLI command.
     * @link https://getcomposer.org/doc/03-cli.md#require Documentation for Composer's `require` command.
     *
     * @throws RuntimeException If the Composer command fails.
     *
     * @return void
     */
    public function installFromComposerFile(string $composerJsonPath): void {
        $cmd = sprintf(
            'composer require --working-dir=%s %s',
            escapeshellarg($this->globalVendorDir),
            escapeshellarg($composerJsonPath)
        );
        exec($cmd, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new RuntimeException(
                sprintf('Failed to install dependencies from %s.', $composerJsonPath)
            );
        }
    }
}
