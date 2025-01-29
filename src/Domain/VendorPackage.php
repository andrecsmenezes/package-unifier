<?php

namespace PackageUnifier\Domain;

/**
 * Represents a Composer package and its version.
 *
 * This class encapsulates the name and version of a Composer package,
 * providing utility methods to retrieve this information. It serves as
 * a simple data structure for representing package metadata.
 *
 * @package PackageUnifier\Domain
 * @since 1.0.0
 */
class VendorPackage {
    /**
     * The name of the Composer package.
     *
     * This property stores the fully qualified name of the Composer package,
     * including the vendor and package name. For example: "vendor/package-name".
     *
     * @since 1.0.0
     * @var string $name The name of the Composer package.
     */
    private string $name;

    /**
     * The version of the Composer package.
     *
     * This property stores the specific version of the package being used.
     * For example: "1.2.3".
     *
     * @since 1.0.0
     * @var string $version The version of the Composer package.
     */
    private string $version;

    /**
     * Initializes the VendorPackage instance with the package name and version.
     *
     * @param string $name The name of the Composer package.
     * @param string $version The version of the Composer package.
     * @since 1.0.0
     */
    public function __construct(string $name, string $version) {
        $this->name = $name;
        $this->version = $version;
    }

    /**
     * Retrieves the name of the Composer package.
     *
     * This method returns the fully qualified name of the Composer package,
     * including the vendor and package name.
     *
     * @since 1.0.0
     *
     * @return string The name of the package.
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * Retrieves the version of the Composer package.
     *
     * This method returns the version of the Composer package being used,
     * as specified in its metadata or dependencies file.
     *
     * @since 1.0.0
     *
     * @return string The version of the package.
     */
    public function getVersion(): string {
        return $this->version;
    }
}
