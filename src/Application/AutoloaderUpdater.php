<?php

namespace PackageUnifier\Application;

use RuntimeException;

class AutoloaderUpdater {
    private string $globalVendorDir;

    public function __construct(string $globalVendorDir) {
        $this->globalVendorDir = $globalVendorDir;
    }

    public function updateAutoloader(): void {
        $cmd = sprintf('composer dump-autoload --working-dir=%s', escapeshellarg($this->globalVendorDir));
        exec($cmd, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new RuntimeException('Failed to update the Composer autoloader.');
        }
    }
}
