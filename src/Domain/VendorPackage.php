<?php

namespace PackageUnifier\Domain;

class VendorPackage {
    private string $name;

    private string $version;

    public function __construct(string $name, string $version) {
        $this->name = $name;
        $this->version = $version;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getVersion(): string {
        return $this->version;
    }
}
