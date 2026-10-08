# Design: WordPress bootstrap safety

Entry: package-unifier.php guards ABSPATH, checks plugin-local vendor/autoload.php, reports missing dependencies only in administrator UI, and otherwise registers safe WordPress lifecycle hooks.

Activation and request hooks perform no Composer installation, vendor scans, or filesystem mutation.

Runtime mutation classes remain research-only and must not become reachable from public hooks.

Verification: PHP lint plus scripts/validate_safe_bootstrap.py; observed runtime-safety Actions success. Official OpenSpec CLI strict validation is an additional outstanding gate.

Rollback: retain fail-closed behavior; never restore removed source exporter or global-autoload boot.
