<?php

namespace PackageUnifier\Application;

use PackageUnifier\Domain\Plugin;
use PackageUnifier\Infrastructure\ComposerService;

class VendorScanner {
    public function scan(): void {
        $plugins = $this->listPlugins();

        foreach ($plugins as $pluginPath) {
            $plugin = new Plugin($pluginPath);
            if ($plugin->hasVendor()) {
                $this->processPlugin($plugin);
            }
        }
    }

    private function listPlugins(): array {
        return glob(WP_CONTENT_DIR . '/plugins/*', GLOB_ONLYDIR) ?? [];
    }

    private function processPlugin(Plugin $plugin): void {
        $composerService = new ComposerService();

        if ($plugin->hasComposerJson()) {
            $composerService->installDependencies($plugin->getComposerJsonPath());
        } else {
            $composerService->movePackages($plugin->getVendorPath());
        }
    }
}
