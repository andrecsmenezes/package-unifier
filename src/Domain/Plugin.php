<?php

namespace PackageUnifier\Domain;

class Plugin {
    private string $path;

    public function __construct(string $path) {
        $this->path = $path;
    }

    public function hasVendor(): bool {
        return is_dir($this->getVendorPath());
    }

    public function hasComposerJson(): bool {
        return file_exists($this->getComposerJsonPath());
    }

    public function getVendorPath(): string {
        return $this->path . '/vendor';
    }

    public function getComposerJsonPath(): string {
        return $this->path . '/vendor/composer.json';
    }
}
