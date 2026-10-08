<?php

declare(strict_types=1);

const TEXT_DOMAIN = 'package-unifier';

$GLOBALS['pu_hooks'] = [];
$GLOBALS['pu_activation'] = [];
$GLOBALS['pu_deactivation'] = [];
$GLOBALS['pu_languages'] = [];

function check(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function add_action(string $hook, callable $callback): void
{
    $GLOBALS['pu_hooks'][$hook][] = $callback;
}

function esc_html__(string $message, string $domain): string
{
    check($domain === TEXT_DOMAIN, 'Notice text domain mismatch.');
    return htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
}

function register_activation_hook(string $file, callable $callback): void
{
    $GLOBALS['pu_activation'][$file] = $callback;
}

function register_deactivation_hook(string $file, callable $callback): void
{
    $GLOBALS['pu_deactivation'][$file] = $callback;
}

function load_plugin_textdomain(string $domain, bool $deprecated, string $relativePath): void
{
    $GLOBALS['pu_languages'][] = [$domain, $deprecated, $relativePath];
}

define('ABSPATH', sys_get_temp_dir() . '/package-unifier-test-wp/');

$root = dirname(__DIR__);
$fixtureDir = sys_get_temp_dir() . '/pu-bootstrap-' . bin2hex(random_bytes(8));
check(mkdir($fixtureDir, 0700), 'Failed to create isolated plugin directory.');
$fixture = $fixtureDir . '/package-unifier.php';

try {
    check(copy($root . '/package-unifier.php', $fixture), 'Failed to copy bootstrap fixture.');

    require $fixture;
    check(isset($GLOBALS['pu_hooks']['admin_notices']), 'Missing local vendor did not register an admin notice.');
    check($GLOBALS['pu_activation'] === [], 'Missing local vendor registered an activation hook.');
    check(!isset($GLOBALS['pu_hooks']['plugins_loaded']), 'Missing local vendor registered runtime hooks.');

    ob_start();
    ($GLOBALS['pu_hooks']['admin_notices'][0])();
    $notice = ob_get_clean();
    check(is_string($notice) && str_contains($notice, 'Package Unifier is inactive:'), 'Expected translated admin notice missing.');
    check(str_contains($notice, 'notice-error'), 'Expected WordPress error notice missing.');

    require $root . '/package-unifier.php';
    check(count($GLOBALS['pu_activation']) === 1, 'Local vendor did not register exactly one activation hook.');
    check(count($GLOBALS['pu_deactivation']) === 1, 'Local vendor did not register exactly one deactivation hook.');
    check(count($GLOBALS['pu_hooks']['plugins_loaded'] ?? []) === 1, 'Runtime registered unexpected plugin hooks.');

    ($GLOBALS['pu_hooks']['plugins_loaded'][0])();
    check(
        $GLOBALS['pu_languages'] === [[TEXT_DOMAIN, false, 'package-unifier/languages']],
        'Text-domain registration mismatch.'
    );

    foreach ($GLOBALS['pu_activation'] as $callback) {
        $callback();
    }
    foreach ($GLOBALS['pu_deactivation'] as $callback) {
        $callback();
    }

    echo "WORDPRESS_BOOTSTRAP_FIXTURE=READY\n";
} finally {
    if (is_file($fixture)) {
        unlink($fixture);
    }
    rmdir($fixtureDir);
}
