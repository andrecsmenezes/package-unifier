# Design: WordPress bootstrap safety

Entry: package-unifier.php guards ABSPATH, checks plugin-local vendor/autoload.php, reports missing dependencies only in administrator UI, and otherwise registers safe WordPress lifecycle hooks.

Activation and request hooks perform no Composer installation, vendor scans, or filesystem mutation.

Runtime mutation classes remain research-only and must not become reachable from public hooks.

Verification: PHP lint, guarded-bootstrap source checks, isolated PHP fixture and reachable-mutation regression; hosted runtime-safety and official OpenSpec 1.13.1 strict validation observed green on main. Real WordPress integration: hosted `@wordpress/env` workflow required; record actual CI outcome before marking complete.

Rollback: retain fail-closed behavior; never restore removed source exporter or global-autoload boot.
