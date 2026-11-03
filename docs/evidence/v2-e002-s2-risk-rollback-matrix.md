# E-PU-V2-002 / S2 — Compatibility, risk and rollback decision matrix

Status: evidence-backed architecture assessment; no consolidation authorization.
Approved scope digest: 23662884e2286b7e2e46bdec6e0ade4a29b7c0c450d0d582211ae22272ab908f
Source main: 839fa7413e565e33cedc2819a8017c195fd2c2d7
Source: archived backlog D01, C02, D02; merged PR #34; current README, composer.json and safe WordPress bootstrap.
Related input schema: docs/evidence/v2-e002-manifest-intake.schema.json

## Demand and decision boundary

A real cross-plugin dependency conflict, named owner need, and version/load-order
compatibility matrix remain UNVERIFIED. This is not evidence that package sharing
is safe, desirable or supported. No production WordPress site was inspected.

The current fail-closed plugin-local vendor/autoload.php behavior stays the default.
No option below is approval to enable runtime Composer, scan other plugins, or
move vendor or node_modules packages.

## Alternatives

| Dimension | A — Retain local autoload (current/default) | B — Offline metadata comparison (conditional) | C — Isolated CLI-only compatibility research (not approved) |
| --- | --- | --- | --- |
| Input | Current plugin source and lock | Two or more separately authorized, sanitized manifest/lock metadata samples | Approved exact dependency inputs, sandbox configuration and owner decision |
| Writes | None | None | Disposable sandbox only; never production or another plugin root |
| Output | Preserve current behavior | Constraint intersection, incompatible requirements, unknowns and provenance | Explicit compatibility, load-order and isolation findings with negative test evidence |
| Autoload risk | Local isolation retained | Report only; no autoload evaluation in live WordPress | Class-name collisions, Composer prefix/PSR-4 ownership and load-order must be tested in isolation |
| Version risk | Per-plugin constraints remain separate | Compare exact constraints and locked versions; never infer runtime resolution | Fail closed on conflicting semver, ambiguous library ownership or unapproved constraint changes |
| Atomicity | No mutation | No mutation | Any future filesystem operation would require a documented atomic transaction and verified rollback; research cannot bypass this gate |
| Concurrency | No shared mutable state introduced | Immutable inputs, no shared output mutation | Exclusive lock, cancellation rules and safe recovery required before any mutating design |
| Recovery | Current local autoload still works | Discard offline report and retain A | Delete disposable state; restore from verified pre-action snapshot if future approved sandbox mutation was attempted |
| Authorization | No new approval needed to preserve current state | Explicit disclosure permission for each metadata set | Separate named-owner authorization, fixed scope digest, threat model and isolated change approval |
| Decision today | KEEP | BLOCKED: owner-supplied metadata not provided | BLOCKED: independent approval and evidence not provided |

## Threat, test and rejection matrix

| Threat or failure | Testable isolated scenario | Expected decision |
| --- | --- | --- |
| Missing provenance | Plugin name or source commit SHA omitted from supplied metadata | Reject evidence input; no comparison or mutation |
| Secret exposure | Input contains unknown credential/script/path fields or unreviewed free text | Reject schema-unknown data and require manual redaction; do not store or log secrets |
| Conflicting semver | Two explicitly supplied plugins require disjoint versions of one package | Report CONFLICT; do not force installation or unify classloaders |
| Unknown runtime resolution | A locked version or target PHP/WordPress version is unavailable | Report UNKNOWN; no compatibility PASS without runtime evidence |
| PSR-4 or class collision | Two packages export the same unisolated class symbol | Reject global autoload; preserve independent local autoload |
| Composer hooks/scripts | A manifest would invoke Composer scripts or plugins during comparison | B never executes Composer; C requires separately approved sandbox and disabled untrusted scripts |
| Uncontrolled load order | WordPress plugin boot order can change which class definition wins | Reject any shared-runtime design until deterministic order and isolation tests pass |
| Interrupted file operation | Simulated failure occurs between staging and commit of a proposed future operation | No live writes; for future authorized design require atomic commit and verified restoration |
| Concurrent CLI writer | Two instances attempt a shared repository operation | Reject second writer; require stale-lease recovery before further mutation |
| Disk/permission failure | Staging area is full or rollback access is absent | Fail before mutation; never leave a partially shared vendor tree |
| Incomplete backup | Snapshot differs from original source/blob or restore validation fails | NO-GO; do not begin any mutating prototype |
| WordPress boot regression | Local vendor missing, plugin activation fails, or public request fatals in sandbox | NO-GO; preserve fail-closed bootstrap until WordPress integration passes |
| Unauthorized source scan | Proposed tool attempts to enumerate installed plugins or read unrelated directories | Reject operation and require new explicit owner-approved scope |
| Unsupported Node dependency assumptions | node_modules packages are requested without source metadata and approved design | OUT OF SCOPE; do not infer Node package compatibility from Composer data |

## Architecture limits and responsibility

- WordPress request and plugin activation code remain non-mutating and local-only.
- The existence of a local Composer installer dependency does not prove a
  cross-plugin conflict or authorize reusing a shared vendor tree.
- JSON Schema restricts the accepted metadata shape. It is not a secret
  scanner; the input owner must redact credentials and private information.
- Semver and Composer plugin/script behavior require verified source,
  version-locked CLI and isolated tests before option C can be proposed as
  anything beyond research.
- Any future mutating prototype needs separate explicit approval, reversible
  atomic design, owner boundary, backup/restore proof, concurrency tests,
  rollback and WordPress integration before application code can change.
- No automatic dependency consolidation can be enabled by this document.

## Task acceptance versus final owner decision

This matrix completes the bounded assessment T1 / T1-ST1, but NOT the
owner decision T2. The safe, non-operational recommendation is A as current
default. The owner must explicitly approve any change of option or scope.
Dependent offline comparison S1/T2 remains blocked without supplied metadata.
