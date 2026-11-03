<?php
/**
 * Regression tests for owner-supplied Composer metadata comparison.
 *
 * @package PackageUnifier
 */

declare(strict_types=1);

require_once __DIR__ . '/compare_composer_metadata.php';

use InvalidArgumentException;
use PackageUnifier\Tools\ComposerMetadataComparator;

/**
 * Require an expected scalar or structured result.
 *
 * @param mixed  $expected Expected value.
 * @param mixed  $actual   Actual value.
 * @param string $message  Assertion description.
 * @return void
 */
function expectSame($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException($message);
    }
}

/**
 * Require a strict evidence validation failure.
 *
 * @param array<string, mixed> $payload Invalid input.
 * @return void
 */
function expectRejected(array $payload): void
{
    try {
        ComposerMetadataComparator::compare($payload);
    } catch (InvalidArgumentException $exception) {
        return;
    }

    throw new RuntimeException('Invalid metadata was accepted.');
}

$evidence = [
    'schema_version' => 'v2-offline-composer-evidence-1',
    'evidence_kind' => 'explicitly_provided_metadata',
    'owner_authorized_offline_review' => true,
    'observed_date' => '2026-11-03',
    'plugins' => [
        [
            'plugin_slug' => 'synthetic-alpha',
            'source_commit_sha' => str_repeat('a', 40),
            'dependencies' => [
                [
                    'package' => 'example/shared',
                    'constraint' => '^1.0',
                    'locked_version' => 'v1.1.0',
                ],
                [
                    'package' => 'example/alpha-only',
                    'constraint' => '^2.0',
                    'locked_version' => null,
                ],
            ],
        ],
        [
            'plugin_slug' => 'synthetic-beta',
            'source_commit_sha' => str_repeat('b', 40),
            'dependencies' => [
                [
                    'package' => 'example/shared',
                    'constraint' => '^2.0',
                    'locked_version' => 'v2.1.0',
                ],
            ],
        ],
    ],
];

$tests = 0;
$report = ComposerMetadataComparator::compare($evidence);
expectSame('OFFLINE_METADATA_ONLY', $report['status'], 'Unexpected report status.');
expectSame(1, $report['shared_package_count'], 'Expected one shared package.');
expectSame('example/shared', $report['shared_packages'][0]['package'], 'Wrong package owner.');
expectSame('TEXT_DIFFERENT', $report['shared_packages'][0]['constraint_relation'], 'Constraint difference missing.');
expectSame('DIFFERENT', $report['shared_packages'][0]['locked_version_relation'], 'Lock difference missing.');
expectSame(
    'UNKNOWN_REQUIRES_SEMVER_AND_RUNTIME_EVIDENCE',
    $report['shared_packages'][0]['compatibility'],
    'Metadata must not certify runtime compatibility.'
);
expectSame(false, $report['cross_plugin_conflict_verified'], 'Incompatibility was improperly inferred.');
expectSame('RETAIN_PLUGIN_LOCAL_AUTOLOAD', $report['recommended_operation'], 'Unexpected operation.');
expectSame(false, $report['production_scan_performed'], 'A scan was improperly claimed.');
$tests++;

$reordered = $evidence;
$reordered['plugins'] = array_reverse($reordered['plugins']);
expectSame($report, ComposerMetadataComparator::compare($reordered), 'Output is not deterministic.');
$tests++;

$identical = $evidence;
$identical['plugins'][1]['dependencies'][0]['constraint'] = '^1.0';
$identical['plugins'][1]['dependencies'][0]['locked_version'] = 'v1.1.0';
$sameReport = ComposerMetadataComparator::compare($identical);
expectSame('TEXT_IDENTICAL', $sameReport['shared_packages'][0]['constraint_relation'], 'Identical text missed.');
expectSame('IDENTICAL', $sameReport['shared_packages'][0]['locked_version_relation'], 'Identical lock missed.');
expectSame(false, $sameReport['cross_plugin_conflict_verified'], 'Equal locks do not prove compatibility.');
$tests++;

$unknown = $evidence;
$unknown['plugins'][1]['dependencies'][0]['locked_version'] = null;
expectSame(
    'UNKNOWN',
    ComposerMetadataComparator::compare($unknown)['shared_packages'][0]['locked_version_relation'],
    'Unknown locked version was not preserved.'
);
$tests++;

$separate = $evidence;
$separate['plugins'][1]['dependencies'][0]['package'] = 'example/beta-only';
expectSame(0, ComposerMetadataComparator::compare($separate)['shared_package_count'], 'False shared dependency.');
$tests++;

$bad = $evidence;
$bad['owner_authorized_offline_review'] = false;
expectRejected($bad);
$tests++;

$bad = $evidence;
$bad['observed_date'] = '2026-02-30';
expectRejected($bad);
$tests++;

$bad = $evidence;
$bad['plugins'] = [$evidence['plugins'][0]];
expectRejected($bad);
$tests++;

$bad = $evidence;
$bad['plugins'][1]['plugin_slug'] = 'synthetic-alpha';
expectRejected($bad);
$tests++;

$bad = $evidence;
$bad['plugins'][1]['source_commit_sha'] = 'invalid';
expectRejected($bad);
$tests++;

$bad = $evidence;
$bad['plugins'][0]['dependencies'][0]['scripts'] = ['post-install-cmd' => 'unexpected'];
expectRejected($bad);
$tests++;

$bad = $evidence;
$bad['plugins'][0]['dependencies'][0]['constraint'] = '^1.0; remove files';
expectRejected($bad);
$tests++;

$bad = $evidence;
$bad['plugins'][0]['dependencies'][] = $bad['plugins'][0]['dependencies'][0];
expectRejected($bad);
$tests++;

$bad = $evidence;
$bad['plugins'][1]['dependencies'][0]['package'] = '../outside';
expectRejected($bad);
$tests++;

$bad = $evidence;
$bad['unexpected'] = 'extra';
expectRejected($bad);
$tests++;

echo 'OFFLINE_COMPOSER_METADATA_TEST=PASS cases=' . $tests . PHP_EOL;
