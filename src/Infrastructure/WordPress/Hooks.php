<?php

declare(strict_types=1);

namespace PackageUnifier\Infrastructure\WordPress;

use PackageUnifier\Shared\Config;

final class Hooks
{
    public static function register(): void
    {
        // Runtime Composer execution is intentionally disabled pending transactional design.
        add_action('plugins_loaded', [self::class, 'loadTextDomain']);
    }

    public static function loadTextDomain(): void
    {
        load_plugin_textdomain(
            Config::TEXT_DOMAIN,
            false,
            dirname(Config::BASENAME) . Config::LANGUAGES_PATH
        );
    }
}
