# package-unifier

state=experimental;production_ready=false;runtime_consolidation=disabled
runtime=PHP>=8.0;WordPress;Composer-local-autoload
scope=WordPress plugin dependency consolidation research;never assume safe cross-plugin package coalescing

## Canonical ownership

entrypoint=package-unifier.php
domain=src/Domain/*
application=src/Application/*
composer_adapter=src/Infrastructure/ComposerService.php
wordpress_hooks=src/Infrastructure/WordPress/Hooks.php
activation=src/Infrastructure/WordPress/PluginActivator.php
config=src/Shared/Config.php
dependency_manifest=composer.json
lockfile=composer.lock

## Enforced safety

- runtime autoload: local `vendor/autoload.php` only; global classloader prohibited until semver compatibility and load-order validation.
- missing local autoload: no public fatal; admin-only notice; plugin bootstrap returns.
- activation: no global vendor creation, package installation, or filesystem writes.
- WordPress request hooks: localization only; no Composer invocation, vendor scanning, or package changes.
- `VendorScanner`, `ComposerService`, `DependencyInstaller`, and `AutoloaderUpdater` remain experimental implementation references; do not invoke in production.
- runtime Composer operations cannot be reenabled without a reviewed OpenSpec change, verified authorization, conflict resolution, atomic transactions, concurrency control, rollback, and dedicated test coverage.
- Locale priority for future user-facing UI: `pt-BR > en > es`; technical docs: English only.

## Validation

```sh
php -l package-unifier.php
find src -type f -name '*.php' -exec php -l {} \;
python3 scripts/validate_safe_bootstrap.py
```

## Open issues

P0: verify boot via WordPress integration test; establish official OpenSpec CLI/init/update/validate workflow; add adversarial CODE_DEDUPLICATION_AGENT, CLEAN_CODE_AGENT, ARCHITECTURE_AGENT, I18N_AGENT contracts.
P1: remove committed `vendor/` and generated `php_files_report.txt` after reproducibility review; consolidate duplicate Composer execution classes; remove dead code; resolve lifecycle ownership and version conflicts.
P2: implement explicit administrator/CLI-only dry-run plan, package compatibility verification, transactional install and rollback only after requirements/evidence permit.
