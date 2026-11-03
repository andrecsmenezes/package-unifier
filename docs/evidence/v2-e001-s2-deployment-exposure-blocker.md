# E-PU-V2-001 / S2-T2-ST1 — Authorized deployment exposure evidence boundary

State: no authorized deployment URL/release supplied; parent S06 exposure check remains BLOCKED.
Observation date: 2026-11-03 (America/Sao_Paulo).
Approved scope digest: 23662884e2286b7e2e46bdec6e0ade4a29b7c0c450d0d582211ae22272ab908f
Source baseline: a99e46e3da4d8c8d8e36b8292b914cd7ebe39903.

## Local source safety evidence

- Source specification: openspec/specs/package-unifier/spec.md prohibits unauthenticated root-level source exporters/reports.
- Source validator: scripts/validate_safe_bootstrap.py rejects root export.php and php_files_report.txt.
- PR #42 and PR #46 passed the exact-head runtime-safety validation; the latter also passed real WordPress integration in isolated CI.
- These are **repository/CI findings only**. They do not prove any deployed web endpoint is inaccessible, any release reached production, or that a server has deleted legacy files.

## External verification blocker

| Required field | Current observation | Status |
| --- | --- | --- |
| Authorized site identifier and URL | Not supplied | MISSING |
| Named owner/operator permission for bounded anonymous HTTP probes | Not supplied | MISSING |
| Installed Package Unifier release and deployment timestamp | Not supplied | MISSING |
| Specific allowed plugin base path and source-export URL targets | Not supplied | MISSING |
| Observed anonymous HTTP response and timestamp | No HTTP request was made | NOT_TESTED |

No production site or anonymous URL has been guessed, crawled, probed, or contacted. The absence of a URL in current evidence does **not** mean no deployment exists.

## Minimum read-only proof plan

After the operator explicitly identifies the authorized target and source paths:

1. Record authorization, specific site/release, date/time, and the exact two previously identified legacy root filenames.
2. Request only the exact approved anonymous GET targets, with no credentials, no sweeping enumeration and no arbitrary guessed paths.
3. Record only bounded HTTP status, content type, redacted headers, whether source code/report data was exposed, timestamps, and deployment release.
4. Treat HTTP 200 containing source/report data as FAIL requiring owner response; treat 403/404 as a bounded endpoint result, not a guarantee that all paths are safe.
5. Validate any CDN/cache/rewrite behavior and the actual deployed release with the operator; do not draw sitewide conclusions from limited negative responses.
6. Keep raw secrets, detailed source exports and sensitive response bodies out of the repository.

## Acceptance boundary

The documented **no-authorized-deployment-reference blocker** satisfies the evidence-status alternative for subtask E-PU-V2-001-S2-T2-ST1, but no external S06 check has occurred. Parent E-PU-V2-001-S2-T2 remains BLOCKED and must not be marked DONE until exact authorized deployment and readback evidence are supplied.
