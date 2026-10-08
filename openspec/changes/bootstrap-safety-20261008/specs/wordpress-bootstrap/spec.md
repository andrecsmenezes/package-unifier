# Capability: wordpress-bootstrap

## Requirement: guarded local bootstrap

The plugin MUST stop outside WordPress and MUST NOT load a global WordPress-level Composer autoloader.

### Scenario: no WordPress runtime

Given the plugin entrypoint executes without ABSPATH defined,
when boot starts,
then it SHALL exit before installing hooks or loading dependencies.

### Scenario: local dependencies absent

Given ABSPATH exists but plugin-local vendor/autoload.php is absent,
when boot starts,
then the plugin SHALL register an admin-only notice and return without fatal error or global autoload fallback.

## Requirement: no request or activation-time mutation

The plugin MUST NOT invoke Composer, scan unrelated plugins, or mutate vendor directories during WordPress request or activation lifecycle.

### Scenario: WordPress plugin activation

Given an administrator activates the plugin,
when activation hooks run,
then no Composer installation or global vendor filesystem mutation SHALL occur.

## Requirement: no public source exporter

The plugin repository MUST NOT include root-level unauthenticated PHP source exporter or generated source-report artifacts.

### Scenario: source safety check

Given export.php or php_files_report.txt is present at the plugin root,
when the repository security validator runs,
then validation SHALL fail.
