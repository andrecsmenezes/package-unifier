# E-PU-V2-002 / S2 — Evidence-based NO-GO decision

Status: NO-GO for a runtime dependency-consolidation change with current evidence.
Scope: E-PU-V2-002-S2-T2 and E-PU-V2-002-S2-T2-ST1.
Approval digest: 23662884e2286b7e2e46bdec6e0ade4a29b7c0c450d0d582211ae22272ab908f
Baseline: 39e03294282a75a382908526721f5090b27122f8
Source precedent: historico/legacy-openspec-backlog.md (D01, D02, C02), merged PR #34.
Evidence: docs/evidence/v2-e002-s1-demand-assessment.md and docs/evidence/v2-e002-s2-risk-rollback-matrix.md.

## Decision

**NO-GO: do not introduce shared/global vendor loading, automatic dependency
coalescing or cross-plugin Composer operations into WordPress runtime.**

**Retain A (existing plugin-local vendor/autoload.php) without modification.**

This is a conservative technical decision that no implementation should begin
on the evidence currently available. It is not an assertion that the
repository owner formally approved option B, option C, or a product-level
decision. It authorizes no new code, no release, no deployment and no vendor
mutation.

## Why the result is NO-GO

- No measured dependency collision involving two named, authorized WordPress
  plugins has been supplied. D01's demand remains UNVERIFIED.
- No validated version-constraint intersection, load-order collision analysis,
  isolation and transaction guarantees or owner-approved rollback strategy
  exists for a global runtime solution.
- The shipped plugin is experimental and production_ready=false, and its
  bootstrap explicitly uses local Composer autoload.
- Version-conflict and filesystem safety cannot be inferred from one plugin's
  composer.json/composer.lock, a historical PR title, or hypothetical examples.
- Node dependency unification is not established by this PHP/Composer evidence.

## Alternatives considered

| Option | Decision | Reconsideration condition |
| --- | --- | --- |
| A: retain plugin-local autoload | **KEEP**; current default, no code change | Continual safety tests and compatibility updates |
| B: read-only offline comparison | **BLOCKED_EXTERNAL** | Two or more sanitized, explicitly supplied metadata records and owner authorization |
| C: isolated CLI compatibility research | **NOT_AUTHORIZED** | Named-owner approval of bounded experimental scope after B is evidence-backed |
| Runtime global consolidation | **NO-GO** | Separate OpenSpec proposal, owner authorization, compatibility/atomicity/concurrency/rollback evidence, negative tests and exact-head CI |

## Future owner decision boundary

Before a later GO decision, obtain all of:
1. Explicitly named owner and authorized plugin set with source provenance.
2. Measured cross-plugin compatibility problem and sanitized constraints.
3. Version/semver and PHP/WordPress compatibility matrix.
4. Deterministic classloader isolation and load-order design.
5. Reversible, atomic, concurrent-writer-safe sandbox design and tests.
6. Dedicated backup/recovery, failure injection and WordPress integration.
7. A separately approved OpenSpec change and exact-SHA CI; review and release
   authorization are separate from a decision to research.

Any absent condition keeps the decision at NO-GO. This document completes
the finite evidence-based NO-GO deliverable, **not** a future owner approval
to run Composer, scan installed plugins, or modify vendor files.

## Current backlog dependency

E-PU-V2-002-S1-T2 remains WAITING_EXTERNAL until owner-supplied metadata is
available and verified. The epic must remain open for that task or be closed
only through an explicit reviewed scope decision, never by silent waiver.
