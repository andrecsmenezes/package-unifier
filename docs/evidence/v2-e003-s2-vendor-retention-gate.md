# E-PU-V2-003 / S2-T2-ST1 — Keep-versus-remove tracked vendor gate

State: documentary subtask complete only after exact-head CI and merge; the parent removal decision remains BLOCKED.
Observation date: 2026-11-03.
Approved scope digest: 23662884e2286b7e2e46bdec6e0ade4a29b7c0c450d0d582211ae22272ab908f
Evidence: docs/evidence/v2-e003-s2-composer-reproducibility.md; merged PR #46; composer.lock blob 1ad02e6642dbfbf20b6cb20ffb97662960a86880.

## Verified observations versus missing release evidence

- The lock metadata now matches composer.json's PHP ^8.0 constraint; locked composer/installers is v2.3.0.
- Two Composer 2.9.5 / isolated PHP 8.5.4 rebuilds were byte-identical with each other, and each produced 117 vendor files.
- Nine Composer-generated tracked vendor files differ from these isolated rebuilt files, including autoload outputs.
- On PR #46, exact-head OpenSpec, PHP 8.0 runtime-safety and Composer strict validation, independent dependency security, and real wp-env WordPress integration passed.
- No independently signed/installable deployment artifact, atomic delivery protocol, approved production version matrix, live rollback drill or named owner approval to remove vendor is verified.
- The real WordPress core version run by wp-env is not pinned in a tracked config; no deployment PHP/WordPress version is established.

## Decision comparison

| Dimension | KEEP current tracked vendor | REMOVE tracked vendor and distribute a built artifact |
| --- | --- | --- |
| Production dependency delivery | Current repository includes plugin-local vendor | A separate, immutable ZIP/artifact must always include built local autoload and locked packages |
| Build timing | No deployment-time Composer needed | Composer may run only inside approved isolated CI/release build, never WordPress request/activation or production |
| Integrity | Current source and 117 vendor files can be checked together | Artifact must include verifiable checksums, provenance, complete files and required PHP/WordPress matrix |
| Reproducibility | No assumption of byte identity with rebuilt vendor | Must prove byte equality or explicitly explain/review all generated differences across pinned toolchain |
| Missing local autoload | Existing fail-closed admin notice | Fresh installs/upgrades must prove autoload is present; failure requires no partial deployment |
| Atomic upgrade | Preserve existing rollback baseline | Independently verify staging, atomic promote, writer isolation, cancellation and rollback |
| Recovery | Retain approved prior plugin deployment artifact | Retain tested prior artifact and exact restore steps, verify on disposable WordPress before release |
| Owner authorization | No change: keep existing behavior | Requires separate named-owner approval, OpenSpec change, review and release decision |
| Current outcome | KEEP (no filesystem change) | BLOCKED: no approval or full release/recovery evidence |

## Suggested verification gates before any new owner-approved proposal

1. Select exact WordPress/PHP/Composer release targets from authorized deployment evidence; distinguish supported vs EOL PHP.
2. Define an immutable package build with Composer scripts/plugins disabled by default, pinned inputs, code review, provenance and dependency security checks.
3. Demonstrate local autoload and correct activation in disposable WordPress for named PHP/core combinations, including missing vendor and rollback cases.
4. Record the nine observed generated-vendor differences and approve explicit treatment; do not assume that equal counts imply equal bytes.
5. Verify ZIP completeness, exact checksums, read-only release installation, negative tests and restore path, with no production Composer execution.
6. Supply and independently attest atomicity, permissions, concurrency control, backup, recovery, and delivery/rollback ownership.
7. Obtain explicit owner approval for a separately scoped OpenSpec change and a **separate** release/deployment authorization.

If any gate is absent, removal remains NO-GO. This deliverable does not delete, rewrite, or relocate vendor, change Composer versions or runtime autoload, propose global package merging, or deploy anything.

## Explicit parent blocker

E-PU-V2-003-S2-T2 remains BLOCKED. An approved documentation subtask does not constitute permission to delete vendor. Do not close the parent task or the epic from this record alone.
