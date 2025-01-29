<?php

namespace PackageUnifier\Domain;

/**
 * Represents a WordPress plugin and its dependencies.
 *
 * This class provides utility methods to check for the existence of specific
 * dependency-related files and directories in a WordPress plugin's folder. It
 * is used to facilitate the detection and processing of vendor folders and
 * `composer.json` files.
 *
 * @package PackageUnifier\Domain
 * @since 1.0.0
 */
class Plugin {
    /**
     * Absolute path to the plugin directory.
     *
     * This property holds the full path to the directory where the plugin's files are located.
     *
     * @since 1.0.0
     * @var string $path The absolute path to the plugin directory.
     */
    private string $path;

    /**
     * Initializes the Plugin instance with the provided path.
     *
     * @param string $path The absolute path to the plugin's directory.
     * @since 1.0.0
     */
    public function __construct(string $path) {
        $this->path = $path;
    }

    /**
     * Checks if the plugin contains a `vendor` directory.
     *
     * This method verifies if the `vendor` directory exists within the plugin's
     * directory, indicating the presence of dependency files.
     *
     * @since 1.0.0
     *
     * @uses self::getVendorPath() To retrieve the vendor path.
     * @link https://www.php.net/manual/en/function.is-dir.php Documentation for `is_dir`.
     *
     * @return bool True if the `vendor` directory exists, false otherwise.
     */
    public function hasVendor(): bool {
        return is_dir($this->getVendorPath());
    }

    /**
     * Checks if the plugin contains a `composer.json` file in its vendor directory.
     *
     * This method determines whether the plugin explicitly defines its
     * dependencies using Composer by checking for a `composer.json` file.
     *
     * @since 1.0.0
     *
     * @uses self::getComposerJsonPath() To retrieve the path to the `composer.json` file.
     * @link https://www.php.net/manual/en/function.file-exists.php Documentation for `file_exists`.
     *
     * @return bool True if the `composer.json` file exists, false otherwise.
     */
    public function hasComposerJson(): bool {
        return file_exists($this->getComposerJsonPath());
    }

    /**
     * Retrieves the path to the `vendor` directory within the plugin.
     *
     * This method constructs and returns the absolute path to the plugin's
     * `vendor` directory. The directory contains the Composer dependencies
     * for the plugin, if available.
     *
     * @since 1.0.0
     *
     * @return string The absolute path to the `vendor` directory.
     */
    public function getVendorPath(): string {
        return $this->path . '/vendor';
    }

    /**
     * Retrieves the path to the `composer.json` file within the plugin's vendor directory.
     *
     * This method constructs and returns the absolute path to the `composer.json` file
     * located in the plugin's `vendor` directory. The file defines the plugin's
     * Composer dependencies.
     *
     * @since 1.0.0
     *
     * @return string The absolute path to the `composer.json` file.
     */
    public function getComposerJsonPath(): string {
        return $this->path . '/vendor/composer.json';
    }
}
