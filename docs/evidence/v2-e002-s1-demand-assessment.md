# E-PU-V2-002 / S1 — Owner-supplied Composer evidence intake

Approved plan digest: 23662884e2286b7e2e46bdec6e0ade4a29b7c0c450d0d582211ae22272ab908f
Source baseline: 9116d997157c91c4d0b9746faf8beaa6fc243247
Archived D01: historico/legacy-openspec-backlog.md
Merged precedent: PR #34 (D01 alternative comparison)
Current local composer.json blob SHA: 24165ac581ad6d36e233d3b141f3e1c531fe76f4
Status: evidence of cross-plugin incompatibility = UNVERIFIED

## Measured need

The active Package Unifier repository declares PHP ^8.0 and
composer/installers ^2.3, with local PSR-4 autoload and committed lockfile.
The repository alone does NOT establish an actual dependency conflict between
two WordPress plugins, a customer requirement to coalesce packages, or safe
global runtime dependency merging. No externally installed plugin was scanned.

**Current decision boundary:** retain plugin-local vendor/autoload.php,
with automatic Composer installation and global consolidation disabled.

## Safe, owner-authorized metadata intake

Only copy a deliberately selected subset of Composer metadata into an input
validated against v2-e002-manifest-intake.schema.json:

- explicitly supplied plugin slug and source commit SHA;
- dependency package name, declared semver constraint, locked version or null;
- evidence kind, observed date, and explicit owner authorization for *offline*
  read-only review.

Minimum two distinct plugins are required to compare cross-plugin constraints.
Unknown property names, arbitrary free-form manifests, paths, tokens, passwords,
repositories, scripts, authentication, environment variables, and full vendor
trees are not part of the allowed schema. Do not include credentials within
allowed string values: this schema is not a substitute for manual redaction.
Never read unrequested directories or run Composer plugins/scripts against
untrusted manifests.

## Allowed and blocked operations

A: Keep existing plugin-local autoload — default; no approval for changes.
B: Offline read-only metadata comparison — BLOCKED until separately supplied,
sanitized and owner-authorized inputs exist.
C: Isolated opt-in CLI compatibility evaluation — requires separate explicit
approval, sandboxed constraints and test plan before any execution.

No code path may scan all installed plugins, move vendor/node_modules packages,
execute Composer in WordPress requests, create a global classloader, or deploy
changes under this evidence task.

## Completion criteria

The intake schema is available and source-grounded need remains UNVERIFIED.
Follow-up E-PU-V2-002-S1-T2 enters WAITING_EXTERNAL until at least two
authorized, provenance-attributed metadata samples are supplied. Decisions
about version conflict and option C are not inferred from this repository.
