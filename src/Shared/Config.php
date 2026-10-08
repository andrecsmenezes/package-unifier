<?php

namespace PackageUnifier\Shared;

class Config {
    public const PLUGIN_NAME = 'Package Unifier';

    public const VERSION = '1.0.0';

    public const REQUIRED_PHP_VERSION = '8.0';

    public const ROOT_DIR = __DIR__ . '/../../';

    public const BASENAME = 'package-unifier/package-unifier.php';

    public const TEXT_DOMAIN = 'package-unifier';

    public const GLOBAL_VENDOR_DIR = ABSPATH . 'vendor';

    public const GLOBAL_VENDOR_AUTOLOAD = self::GLOBAL_VENDOR_DIR . '/autoload.php';

    public const VENDOR_AUTOLOAD = self::ROOT_DIR . 'vendor/autoload.php';

    public const LANGUAGES_PATH = '/languages';
}
