<?php
/**
 * Compare explicitly supplied Composer metadata without inspecting plugins.
 *
 * @package PackageUnifier
 */

declare(strict_types=1);

namespace PackageUnifier\Tools;

use InvalidArgumentException;
use Throwable;

// Prevent direct web execution of this explicit-input CLI utility.
if (PHP_SAPI !== 'cli') {
    exit;
}

/**
 * Validate owner-authorized metadata and report shared package observations.
 *
 * No Composer execution, semver resolution, network calls, or filesystem
 * operations are performed by this class.
 *
 * @since 1.0.0
 */
final class ComposerMetadataComparator
{
    /**
     * Compare a sanitized metadata payload against a strict allowlist.
     *
     * @since 1.0.0
     *
     * @param array<string, mixed> $payload Explicitly provided evidence.
     * @return array<string, mixed> A non-operational, deterministic report.
     */
    public static function compare(array $payload): array
    {
        self::requireFields(
            $payload,
            ['schema_version', 'evidence_kind', 'owner_authorized_offline_review', 'observed_date', 'plugins']
        );

        if (
            ($payload['schema_version'] ?? null) !== 'v2-offline-composer-evidence-1'
            || ($payload['evidence_kind'] ?? null) !== 'explicitly_provided_metadata'
            || ($payload['owner_authorized_offline_review'] ?? null) !== true
        ) {
            throw new InvalidArgumentException('Explicit owner-authorized evidence is required.');
        }

        $date = $payload['observed_date'] ?? null;
        if (
            !is_string($date)
            || preg_match('/^(\d{4})-(\d{2})-(\d{2})$/D', $date, $parts) !== 1
            || !checkdate((int) $parts[2], (int) $parts[3], (int) $parts[1])
        ) {
            throw new InvalidArgumentException('Invalid observation date.');
        }

        $plugins = $payload['plugins'] ?? null;
        if (
            !is_array($plugins)
            || !self::isList($plugins)
            || count($plugins) < 2
            || count($plugins) > 20
        ) {
            throw new InvalidArgumentException('Provide between two and twenty explicit plugins.');
        }

        $byPackage = [];
        $provenance = [];

        foreach ($plugins as $plugin) {
            if (!is_array($plugin)) {
                throw new InvalidArgumentException('Invalid plugin metadata.');
            }

            self::requireFields($plugin, ['plugin_slug', 'source_commit_sha', 'dependencies']);

            $slug = $plugin['plugin_slug'] ?? null;
            $sha = $plugin['source_commit_sha'] ?? null;
            if (
                !is_string($slug)
                || preg_match('/^[a-z0-9][a-z0-9_-]{0,59}$/D', $slug) !== 1
                || isset($provenance[$slug])
                || !is_string($sha)
                || preg_match('/^[a-f0-9]{40}$/D', $sha) !== 1
            ) {
                throw new InvalidArgumentException('Invalid or duplicate plugin provenance.');
            }

            $dependencies = $plugin['dependencies'] ?? null;
            if (
                !is_array($dependencies)
                || !self::isList($dependencies)
                || count($dependencies) > 100
            ) {
                throw new InvalidArgumentException('Invalid dependency collection.');
            }

            $provenance[$slug] = $sha;
            $seen = [];

            foreach ($dependencies as $dependency) {
                if (!is_array($dependency)) {
                    throw new InvalidArgumentException('Invalid dependency metadata.');
                }

                self::requireFields($dependency, ['package', 'constraint', 'locked_version']);

                $package = $dependency['package'] ?? null;
                $constraint = $dependency['constraint'] ?? null;
                $version = $dependency['locked_version'] ?? null;

                if (
                    !is_string($package)
                    || strlen($package) > 120
                    || preg_match('/^[a-z0-9][a-z0-9._-]*\/[a-z0-9][a-z0-9._-]*$/D', $package) !== 1
                    || isset($seen[$package])
                    || !is_string($constraint)
                    || strlen($constraint) > 100
                    || preg_match('/^[A-Za-z0-9*.^~<>|!=+,& _-]{1,100}$/D', $constraint) !== 1
                    || (
                        $version !== null
                        && (
                            !is_string($version)
                            || strlen($version) > 80
                            || preg_match('/^[A-Za-z0-9._+-]+$/D', $version) !== 1
                        )
                    )
                ) {
                    throw new InvalidArgumentException('Invalid or duplicate dependency metadata.');
                }

                $seen[$package] = true;
                $byPackage[$package][$slug] = [
                    'constraint' => $constraint,
                    'locked_version' => $version,
                ];
            }
        }

        ksort($provenance);
        ksort($byPackage);
        $sharedPackages = [];

        foreach ($byPackage as $package => $owners) {
            if (count($owners) < 2) {
                continue;
            }

            ksort($owners);
            $constraints = array_column($owners, 'constraint');
            $versions = array_column($owners, 'locked_version');

            $sharedPackages[] = [
                'package' => $package,
                'observations' => $owners,
                'constraint_relation' => count(array_unique($constraints)) === 1
                    ? 'TEXT_IDENTICAL'
                    : 'TEXT_DIFFERENT',
                'locked_version_relation' => in_array(null, $versions, true)
                    ? 'UNKNOWN'
                    : (count(array_unique($versions)) === 1 ? 'IDENTICAL' : 'DIFFERENT'),
                'compatibility' => 'UNKNOWN_REQUIRES_SEMVER_AND_RUNTIME_EVIDENCE',
            ];
        }

        return [
            'status' => 'OFFLINE_METADATA_ONLY',
            'observed_date' => $date,
            'provenance' => $provenance,
            'shared_package_count' => count($sharedPackages),
            'shared_packages' => $sharedPackages,
            'cross_plugin_conflict_verified' => false,
            'recommended_operation' => 'RETAIN_PLUGIN_LOCAL_AUTOLOAD',
            'production_scan_performed' => false,
            'composer_executed' => false,
        ];
    }

    /**
     * Require an exact, allowlisted set of object fields.
     *
     * @since 1.0.0
     *
     * @param array<string, mixed> $object Parsed JSON object.
     * @param string[]             $keys   Required field names.
     * @return void
     */
    private static function requireFields(array $object, array $keys): void
    {
        $observed = array_keys($object);
        sort($observed);
        sort($keys);

        if ($observed !== $keys) {
            throw new InvalidArgumentException('Unexpected or missing metadata fields.');
        }
    }

    /**
     * Support JSON array checks on PHP 8.0 without array_is_list().
     *
     * @since 1.0.0
     *
     * @param array<mixed> $items Parsed list.
     * @return bool
     */
    private static function isList(array $items): bool
    {
        $index = 0;

        foreach (array_keys($items) as $key) {
            if ($key !== $index++) {
                return false;
            }
        }

        return true;
    }
}

if (isset($_SERVER['SCRIPT_FILENAME']) && realpath((string) $_SERVER['SCRIPT_FILENAME']) === __FILE__) {
    try {
        if ($argc !== 3 || $argv[1] !== '--input') {
            throw new InvalidArgumentException('Explicit JSON evidence file required.');
        }

        $file = $argv[2];
        if (
            pathinfo($file, PATHINFO_EXTENSION) !== 'json'
            || str_contains($file, '://')
            || is_link($file)
            || !is_file($file)
            || !is_readable($file)
            || filesize($file) === false
            || filesize($file) < 1
            || filesize($file) > 262144
        ) {
            throw new InvalidArgumentException('Invalid evidence file.');
        }

        $data = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data)) {
            throw new InvalidArgumentException('JSON object required.');
        }

        $report = ComposerMetadataComparator::compare($data);
        echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
    } catch (Throwable $exception) {
        fwrite(STDERR, "COMPOSER_EVIDENCE=REJECTED\n");
        exit(1);
    }
}
