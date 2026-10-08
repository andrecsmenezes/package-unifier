# Spec Delta

## Purpose

Define the WordPress plugin's fail-closed bootstrap, local-autoloader isolation, filesystem mutation restrictions, and prohibition of publicly reachable source exports.

## ADDED Requirements

### Requirement: Guarded local bootstrap

The plugin MUST stop outside WordPress and MUST NOT load a WordPress-global Composer autoloader.

#### Scenario: No WordPress runtime

- **WHEN** the plugin entrypoint executes without ABSPATH defined
- **THEN** it SHALL exit before installing hooks or loading dependencies

#### Scenario: Local dependencies absent

- **WHEN** ABSPATH exists but plugin-local vendor/autoload.php is absent
- **THEN** it SHALL register an administrator-only notice and stop without fatal error or global-autoload fallback

### Requirement: No request or activation-time mutation

The plugin MUST NOT invoke Composer, scan unrelated plugins, or mutate vendor directories during WordPress request or activation lifecycle.

#### Scenario: WordPress plugin activation

- **WHEN** an administrator activates the plugin
- **THEN** no Composer installation or global vendor filesystem mutation SHALL occur

### Requirement: No public source exporter

The plugin repository MUST NOT contain root-level unauthenticated PHP source exporters or generated source reports.

#### Scenario: Source safety validation

- **WHEN** export.php or php_files_report.txt exists in the plugin root
- **THEN** the repository source validator SHALL fail
