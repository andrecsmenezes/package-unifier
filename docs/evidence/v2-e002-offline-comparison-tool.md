# E-PU-V2-002 / S1-T2 — Offline comparison tool readiness

**Scope:** Prepare a strict, read-only comparison mechanism for explicitly supplied,
owner-authorized sanitized Composer dependency metadata. This is **not** an
actual cross-plugin compatibility report, nor permission to scan a WordPress site.

Approved V2 plan digest:
`23662884e2286b7e2e46bdec6e0ade4a29b7c0c450d0d582211ae22272ab908f`

## Inputs and invocation

The owner must manually supply one JSON file conforming to
`docs/evidence/v2-e002-manifest-intake.schema.json`, containing at least two
different plugin slugs, their verified source commit SHAs, dependency package
names, declared constraints and exact locked versions or null.

The report can then be generated offline:

```sh
php scripts/compare_composer_metadata.php --input /path/to/authorized-sanitized-evidence.json
```

There is no implicit source discovery. The utility requires an explicit local
JSON file; it rejects URL/wrapper paths, symlinks, files larger than 256 KiB,
missing permission/provenance, unknown object fields, duplicate plugins,
duplicate package entries, malformed constraints and malformed versions.

The parser is restricted to the PHP CLI. It does not load WordPress or Composer,
visit websites, read credentials, execute Composer scripts/plugins, enumerate
installed plugins, scan vendor/node_modules, or create a shared autoloader.

## Semantics of the report

The results are stable across input-plugin order and contain:
- exact supplied plugin slug and source commit SHA provenance;
- shared dependency names, owner constraints and locked versions;
- **TEXT_IDENTICAL** or **TEXT_DIFFERENT** for declared constraint strings;
- **IDENTICAL**, **DIFFERENT** or **UNKNOWN** for locked versions;
- explicit **UNKNOWN_REQUIRES_SEMVER_AND_RUNTIME_EVIDENCE** compatibility;
- a default recommendation to **RETAIN_PLUGIN_LOCAL_AUTOLOAD**.

A textual difference is **not** a Composer-semver conflict. Distinct locked
versions can be legitimate independent installations. Equal locked versions do
**not** demonstrate PHP/WordPress support or classloader safety. The comparator
deliberately does not calculate Composer constraint intersections or claim
verified runtime incompatibility. The output always sets
`cross_plugin_conflict_verified=false`.

Its code is covered by `scripts/test_offline_composer_metadata.php`, using
synthetic data only (including unauthorized data and unsafe metadata rejection).
GitHub Actions runs PHP 8.0 syntax and regression checks.

## Acceptance status

The supporting tool preparation and synthetic regression may be accepted on
exact-head CI and verified main merge. **E-PU-V2-002-S1-T2 remains
WAITING_EXTERNAL** until two real, explicitly supplied and authorized
provenance-attributed metadata records are validated and compared.

No real plugin dataset has been provided; no measured cross-plugin dependency
conflict has been proven; no authorization to globally consolidate vendor
packages, remove local vendor or deploy the plugin follows from this tool.
