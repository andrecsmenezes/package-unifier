# E-PU-V2-003 / S2-T2 — Owner-authorized vendor retention decision

**Decision:** KEEP the currently committed plugin-local `vendor/` directory.
**Task:** E-PU-V2-003-S2-T2 (conditional removal gate, not a requirement to remove files).
**Decision type:** Safe no-removal determination; no deployment or release.
**Approved V2 scope digest:** `23662884e2286b7e2e46bdec6e0ade4a29b7c0c450d0d582211ae22272ab908f`.
**Repository baseline:** `f45be4adfa6a37b64771c583125226dcb2f4cf85`.
**User authorization:** On 2026-11-03, the repository owner explicitly authorized requested additions, changes, and deletions while directing continuation of approved epics. This decision uses only the non-destructive retention alternative; **it does not interpret general authorization as permission to bypass verification or deploy to production**.

## Evidence and rationale

1. The plugin's current WordPress entrypoint requires its own `vendor/autoload.php` and fails closed without it. Its runtime is experimental; global dependency consolidation is disabled.
2. The corrected `composer.lock` is compatible with the `composer.json` PHP `^8.0` declaration and pins `composer/installers` v2.3.0. The corrected lock was integrated in [PR #46](https://github.com/andrecsmenezes/package-unifier/pull/46), with exact-head strict Composer, PHP 8.0 source/fixture, real WordPress integration, OpenSpec and independent dependency security success.
3. Two isolated Composer 2.9.5 rebuilds created byte-identical vendor trees of 117 files. **Nine generated files were not byte-identical to the vendor directory currently committed.** This is insufficient evidence for deleting tracked vendor and delivering a rebuilt version as a safe equivalent.
4. There is no documented, independently verified production artifact installer, deployment owner/release identifier, exact deployed PHP/WordPress version matrix, atomic upgrade procedure, live rollback exercise or restore acceptance proof.
5. The current baseline can be preserved **without requiring a new deployment or vendor write**. No measured benefit justifies replacing the delivery model now.

Original source evidence:
- `docs/evidence/v2-e003-s2-composer-reproducibility.md`
- `docs/evidence/v2-e003-s2-vendor-retention-gate.md`
- `composer.json`, `composer.lock`, `package-unifier.php`
- The official GitHub PHP runtime-safety and real WordPress integration workflows.

## Decision matrix

| Alternative | Current disposition | Risk/recovery boundary |
| --- | --- | --- |
| KEEP existing tracked `vendor/` | **SELECTED** | No filesystem or packaging mutation; preserve existing plugin-local autoload and fail-closed bootstrap |
| REMOVE tracked `vendor/` now | **REJECTED** | Missing independent delivery and rollback evidence; rebuilt generated files differ from tracked content |
| CREATE a separately built release ZIP and later consider removal | **FUTURE PROPOSAL ONLY** | Requires approved change, artifact checksums/provenance, exact PHP/WordPress tests, dry-run install and rollback evidence, then separate deployment authorization |

## Enforced check

The `runtime-safety` workflow checks that `vendor/autoload.php` is present,
tracked by Git and can load `Composer\\InstalledVersions` on PHP 8.0.
This prevents silent omission of the current local bootstrap dependency
without an explicitly reviewed change to the safety guard.

No `vendor/` file is deleted, moved or regenerated. No manifest, lockfile,
plugin runtime PHP or global autoloader is modified.

## Acceptance and future owner gate

The finite S2-T2 acceptance is met by **not removing** the tracked vendor while
any required installation, recovery, rollback or separate release authorization
remains unverified. This closes the optional-removal **decision**, not a
production release or a request to perform deletion.

Any future decision to REMOVE must be introduced as a new, separately approved
OpenSpec/release scope after sufficient independent evidence, including:
immutable ZIP artifact and exact checksums; testable install/restore steps;
negative and concurrency tests; version-pinned WordPress/PHP matrix; rollback
of a disposable deployment; separate release-owner signoff. Do not convert
this retained-vendor decision into implicit approval for global consolidation.

**Next task:** E-PU-V2-003-S1-T1 is still blocked on deployed WordPress/PHP/Composer
version evidence; S1-T2 is dependent on that verification.
