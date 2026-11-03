# E-PU-V2-003 / S1-T1-ST1 — Version and support evidence matrix

State: documentary subtask evidence prepared; parent S1-T1 remains BLOCKED on authorized deployment evidence.
Observation date: 2026-11-03 (America/Sao_Paulo).
Approval digest: 23662884e2286b7e2e46bdec6e0ade4a29b7c0c450d0d582211ae22272ab908f
Audited repository baseline: a99e46e3da4d8c8d8e36b8292b914cd7ebe39903
No production credentials, URL, deployment release, server logs, or installed-version evidence were supplied.

## Evidence ownership — declarations are not deployment observations

| Subject | Evidence in the repository | Evidence type | Actual installed/deployed version |
| --- | --- | --- | --- |
| Package Unifier | package-unifier.php: Version 1.0.0 | Plugin header declaration | UNKNOWN; no deployed release is established |
| PHP minimum | package-unifier.php: Requires PHP 8.0; composer.json and composer.lock: php ^8.0 | Manifest/runtime constraint | UNKNOWN |
| WordPress minimum | package-unifier.php: Requires at least 5.6 | Plugin header declaration, not tested compatibility | UNKNOWN |
| Intended WordPress 6.7.1 | Project target supplied in the development specification | Requested target, not deployment evidence | UNKNOWN |
| Local Composer dependency | composer.lock blob 1ad02e6642dbfbf20b6cb20ffb97662960a86880: composer/installers v2.3.0 | Locked package, not installed production version | UNKNOWN |
| Composer build tool | v2-e003-s2-composer-reproducibility.md: Composer 2.9.5 in an isolated disposable sandbox | Verified sandbox-only observation | UNKNOWN |
| PHP build runtime | Same evidence: PHP CLI 8.5.4 in the disposable sandbox | Verified sandbox-only observation | UNKNOWN |
| PHP CI | .github/workflows/runtime-safety.yml: setup-php 8.0; PR #46 passed | Workflow-configured CI runtime | UNKNOWN |
| WordPress integration CI | .github/workflows/wordpress-integration.yml: @wordpress/env 11.17.0; PR #46 passed | Tool version, NOT the WordPress core version inside wp-env | UNKNOWN |
| WordPress core version used in CI | No fixed core version in the repository's .wp-env.json (file absent) | Unpinned/missing provenance; do not infer tested core version | UNKNOWN |

The project target and plugin header do not prove a deployment; CI green results do not establish deployed compatibility. Do not silently increase the minimum WordPress or PHP version without separate approved evidence.

## Official upstream compatibility and support evidence

Sources verified on 2026-11-03:
- WordPress Core PHP compatibility table: https://make.wordpress.org/core/handbook/references/php-compatibility-and-wordpress-versions/
- Official WordPress plugin header semantics: https://developer.wordpress.org/plugins/plugin-basics/header-requirements/
- PHP supported branches: https://www.php.net/supported-versions.php
- PHP unsupported branches: https://www.php.net/eol.php

| PHP branch | Official PHP lifecycle as of 2026-11-03 | WordPress 6.7 upstream matrix | Package Unifier validated exact pair? |
| --- | --- | --- | --- |
| 8.0 | EOL since 2023-11-26 | Y | NO; CI PHP 8.0 fixture alone is not exact WP 6.7 evidence |
| 8.1 | EOL since 2025-12-31 | Y | NO |
| 8.2 | Security-only until 2026-12-31 | Y | NO |
| 8.3 | Security-only until 2027-12-31 | Y | NO |
| 8.4 | Active until 2026-12-31; security until 2028-12-31 | Y | NO |
| 8.5 | Active until 2027-12-31; security until 2029-12-31 | N | NO; isolated PHP 8.5.4 artifact build is not a WordPress 6.7 compatibility test |

Official WordPress compatibility describes WordPress core's supported runtime combinations; it does not certify this plugin or its third-party dependencies. A Y does not replace plugin bootstrap/activation/integration evidence. A PHP branch at EOL remains EOL even if a WordPress compatibility table has Y.

## Missing authorized deployment evidence

A site operator may supply **sanitized metadata only**, without passwords or API tokens:

| Field | Required provenance |
| --- | --- |
| Authorization scope | Named operator/owner permission for this deployment and read-only verification |
| Target | Explicit authorized site identifier and its exact release/deployment commit |
| Observation | Timestamp with timezone and collection method |
| PHP | Runtime version observed in the same environment, not a local laptop or CI |
| WordPress | Installed core version observed in the target |
| Composer | Build/deployment Composer version if used; say NOT_USED or UNKNOWN otherwise |
| Package Unifier | Installed plugin version/hash and whether local vendor/autoload.php exists |
| Compatibility | Non-mutating readback and isolated WP/bootstrap negative tests for exact versions |

Do not commit environment variables, auth.json, cookies, database dumps, host credentials, SSH keys, or full phpinfo() output. Do not access an unprovided production URL. Once owner-approved metadata exists, the parent task may be resumed under a new lease, then the dependent regression task can be scheduled against explicit versions.

## Completion boundary

This subtask completes the **source-backed matrix with precise UNKNOWN values** and unsupported-version distinctions. It does NOT close parent E-PU-V2-003-S1-T1, which requires deployment evidence, nor unblock dependent E-PU-V2-003-S1-T2.
