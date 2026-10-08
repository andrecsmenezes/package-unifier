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
specification=openspec/config.yaml
active_changes=openspec/changes/*
backlog=openspec/BACKLOG.md
agent_contracts=openspec/project/agents/*

## Enforced safety

- runtime autoload: local `vendor/autoload.php` only; global classloader prohibited until semver compatibility and load-order validation.
- missing local autoload: no public fatal; admin-only notice; plugin bootstrap returns.
- activation: no global vendor creation, package installation, or filesystem writes.
- WordPress request hooks: localization only; no Composer invocation, vendor scanning, or package changes.
- `VendorScanner`, `ComposerService`, `DependencyInstaller`, and `AutoloaderUpdater` remain experimental implementation references; do not invoke in production.
- runtime Composer operations cannot be reenabled without a reviewed OpenSpec change, verified authorization, conflict resolution, atomic transactions, concurrency control, rollback, and dedicated test coverage.
- root-level PHP source exporter and generated source report prohibited: both could disclose plugin internals through unauthenticated HTTP access on some WordPress deployments.
- Locale priority for future user-facing UI: `pt-BR > en > es`; technical docs: English only.

## Official OpenSpec

cli=@fission-ai/openspec@1.13.1;init=openspec init --tools codex;validate=openspec validate --all --strict --no-interactive
generated_tool_integrations=CLI_owned;refresh=openspec update;never_handcraft_generated_skills
sync_and_archive_only_after_objective_validation=true

## Validation

```sh
php -l package-unifier.php
find src -type f -name '*.php' -exec php -l {} \;
python3 scripts/validate_safe_bootstrap.py
```

## Work queue

owner=openspec/BACKLOG.md
