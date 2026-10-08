<?php

declare(strict_types=1);

namespace PackageUnifier\Infrastructure\WordPress;

use PackageUnifier\Shared\Config;
use RuntimeException;

final class PluginActivator
{
    public static function activate(): void
    {
        if (!is_file(Config::VENDOR_AUTOLOAD)) {
            throw new RuntimeException('Local Composer autoload is required.');
        }
        // No global vendor mutations on activation.
    }

    public static function deactivate(): void
    {
        // No global side effects to revert.
    }

    public static function init(): void
    {
        Hooks::register();
    }
}
