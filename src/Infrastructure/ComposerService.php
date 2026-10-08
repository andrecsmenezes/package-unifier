<?php

namespace PackageUnifier\Infrastructure;

use RuntimeException;

class ComposerService {
    private string $globalVendorDir;

    public function __construct() {
        $this->globalVendorDir = ABSPATH . 'vendor';
    }

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
