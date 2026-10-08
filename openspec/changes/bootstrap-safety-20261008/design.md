# Design: WordPress bootstrap safety

Entry: package-unifier.php guards ABSPATH, checks plugin-local vendor/autoload.php, reports missing dependencies only in administrator UI, and otherwise registers safe WordPress lifecycle hooks.

Activation and request hooks perform no Composer installation, vendor scans, or filesystem mutation.

Runtime mutation classes remain research-only and must not become reachable from public hooks.

Verification: PHP lint, guarded-bootstrap source checks, isolated PHP fixture and reachable-mutation regression; hosted runtime-safety and official OpenSpec 1.13.1 strict validation observed green on main. Real WordPress integration: hosted `@wordpress/env` plugin lifecycle, missing vendor and public HTTP passed in PR #11 run #37857648046 and main run #37857932855 (2026-10-08). Scope remains bootstrap safety, not approval for global Composer mutation.

Rollback: retain fail-closed behavior; never restore removed source exporter or global-autoload boot.
