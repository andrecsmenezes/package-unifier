# Proposal: WordPress bootstrap safety

State: implemented; runtime-safety GitHub Actions passed; official OpenSpec CLI validation pending.

Scope: formalize existing fail-closed bootstrap and no-mutation constraints; no new dependency-unification capability.

Constraints: never load global autoloader, run Composer in a request/activation, expose root source-report scripts, or change plugin dependency files without an approved and tested separate change.
