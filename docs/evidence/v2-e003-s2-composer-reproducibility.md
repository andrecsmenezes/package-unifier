# E-PU-V2-003 / S2 — Reproducible Composer lock and local artifact evidence

Status: candidate; exact-PR-head CI and guarded merge required.
Approved scope digest: 23662884e2286b7e2e46bdec6e0ade4a29b7c0c450d0d582211ae22272ab908f
Source main: f415de1a948795737b5e5f6f9b4b49bc2a8c9bdd
Task: E-PU-V2-003-S2-T1; subtask E-PU-V2-003-S2-T1-ST1
Source files: composer.json, composer.lock, vendor/, package-unifier.php
Environment: isolated disposable Ubuntu sandbox; PHP 8.5.4 CLI; Composer 2.9.5.
Deployment verification: NOT PERFORMED; actual WordPress/PHP deployed versions remain UNKNOWN.

## Finding: stale Composer lock metadata

The prior tracked composer.lock did not match composer.json:
- composer.json declares PHP `^8.0`, while lock platform metadata declared `>=7.4`;
- prior content-hash and plugin API metadata were stale.

Initial `composer validate --no-check-publish --strict`: FAIL (lock not up to date).
A sandbox-only `composer update --lock --no-install --no-scripts --no-plugins
--no-interaction --no-audit --no-progress` modified metadata without
upgrading packages. The generated lock was reproduced **byte-exactly**
in the implementation branch (Git blob SHA
`1ad02e6642dbfbf20b6cb20ffb97662960a86880`).

| Lock field | Before | After |
| --- | --- | --- |
| content-hash | 170abe34577a4d1f1a9f4dd1b6bb86fc | 772ff9c59956faa05cd160ba429955b5 |
| platform.php | >=7.4 | ^8.0 |
| plugin-api-version | 2.6.0 | 2.9.0 |
| stability-flags | [] | {} |
| platform-dev | [] | {} |
| Locked package | composer/installers v2.3.0 | composer/installers v2.3.0 |

The old lock SHA256 was
`a91065c8625e2273d7819f9552495592cb24b7a2e99de4c42812d7e6762fc6f9`.
The new lock SHA256 is
`9f506ce933b1c69fa1c3d7a4971af7befd4e649fc47b1ff301b24e9e6a75fb3a`.

## Clean build and reproducibility

Two separate clean vendor-directory rebuilds from the corrected immutable
lock were performed using:

```sh
composer install --no-dev --no-scripts --no-plugins --prefer-dist --optimize-autoloader --no-progress --no-interaction
```

Both succeeded with exactly one locked package installed, no version updates,
and no Composer plugins/scripts executed. The resulting vendor file trees
were **byte-identical between the two isolated rebuilds** (117 files).
The sorted file-content digest was:
`5ff628d7a10d5c3afa598a77e56b7fea75e8e6d6b1a0645e1af1e9653ea95a84`.

`vendor/autoload.php` existed after both builds.
The local checks passed:
- `php scripts/test_wordpress_bootstrap.php`: WORDPRESS_BOOTSTRAP_FIXTURE=READY
- `python3 scripts/validate_safe_bootstrap.py`: BOOTSTRAP_SAFETY=READY (11 checks)
- `python3 scripts/validate_reachable_mutation.py`: REACHABLE_MUTATION=READY (4 bootstrap-reachable files)
- `composer validate --no-check-publish --strict`: valid after metadata repair

## Committed vendor comparison and safety limit

The repository also tracks 117 vendor files, but **nine Composer-generated
files differ** from the two clean rebuilds with Composer 2.9.5 (including
autoload metadata). Therefore, file-count equivalence is not a claim of
byte-identical equivalence to the currently committed vendor directory.
The existing tracked vendor content is **not removed or replaced**.

This test establishes isolated reproducibility for the specified lock and
Composer tool version on PHP 8.5.4 only. It does **not** establish compatible
behavior on PHP 8.0, deployed WordPress, all supported Composer versions, or
the production installation. Those require separate versioned evidence,
WordPress integration and review under E-PU-V2-003-S1.

## Required acceptance

- Exact-head PHP 8.0 runtime-safety and strict Composer metadata check PASS.
- Exact-head real WordPress integration PASS, triggered by composer.lock changes.
- Exact-head official OpenSpec and independent dependency-security scan PASS.
- Guarded merge, and exact-ref temporary branch deletion with absence readback.

Vendor-removal task E-PU-V2-003-S2-T2 remains blocked until independently
verified release artifact and deployment/rollback strategy plus explicit
approval. No deployment, global runtime autoload, package unification or
production filesystem mutation is authorized by this evidence.
