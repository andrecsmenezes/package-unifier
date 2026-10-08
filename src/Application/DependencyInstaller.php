<?php

namespace PackageUnifier\Application;

use RuntimeException;

class DependencyInstaller {
    private string $globalVendorDir;

    public function __construct(string $globalVendorDir) {
        $this->globalVendorDir = $globalVendorDir;
    }

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
