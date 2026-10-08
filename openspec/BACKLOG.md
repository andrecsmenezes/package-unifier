# Backlog
rules=English_only;AI_first;source_of_truth=OpenSpec;dedup_findings;adversarial_agent_review;highest_severity_first;merge_main_after_each_stage;delete_completed_temporary_refs;never_invent_work

## P0 security
- [x] Default WordPress bootstrap fail-closed; local autoload only; request/activation Composer mutation disabled.
- [x] Remove unauthenticated root export.php and generated php_files_report.txt; add a regression guard.
- [x] Observe runtime-safety workflow pass on main for export-removal PR #2.
- [ ] Run a WordPress integration test including missing local vendor, activation, and no public fatals.
- [ ] Confirm legacy deployments no longer expose source-export files after deploying new main.

## P0 OpenSpec / agents
- [x] Add official OpenSpec config and a CLI init/list/strict-validation CI workflow.
- [x] Register CODE_DEDUP_AGENT, CLEAN_CODE_AGENT, ARCHITECTURE_AGENT, I18N_AGENT with adversarial questions and bounded ownership.
- [ ] Observe official CLI initialization and strict spec validation green.
- [ ] Commit officially generated tool integration only after running `openspec init/update`; do not imitate generated files.
- [ ] Sync/archived bootstrap-safety change only when all official and runtime gates pass.

## P1 code architecture / dedup
- [ ] Verify reachability of DependencyInstaller, AutoloaderUpdater, VendorScanner and ComposerService; delete truly unused/obsolete implementations only with tests.
- [ ] Audit Composer CLI construction, package-name/path semantics, version constraints, global autoload load-order and failure modes; keep mutation disabled.
- [ ] Remove committed vendor/ only after proving `composer install` reproducibility and artifact/deployment strategy; do not break the local-autoload bootstrap.
- [ ] Eliminate human-oriented/redundant source comments when semantics are already discoverable from types/tests.
- [ ] Add code-level no-mutation gate including all entrypoint-reachable paths; static string checks alone are insufficient.

## P1 i18n
- [ ] Verify all user-facing notices use WordPress gettext with the exact text domain and pt-BR default; add en/es only if reviewed and actually required.
- [ ] Audit locale-sensitive formatting only where runtime behavior exists; technical documents remain English-only.

## P2 scope / decisions
- [ ] Establish whether global dependency consolidation is necessary; compare read-only analysis and explicit CLI-only alternatives.
- [ ] Require plugin compatibility, semver conflict matrix, authorization, sandbox, atomic transactions, concurrency, backups, rollback and integration tests before enabling any Composer/vendor mutation.
- [ ] Revalidate PHP/WordPress support versions and dependencies before production use.

## Branch lifecycle
- [ ] Delete merged PR #1 branch `fix/failclosed-wordpress-bootstrap-20261007` after exact head verification.
- [ ] Delete merged PR #2 branch `fix/remove-public-code-exporter-20261008` after exact head verification.
- [ ] For each new completed change, remove the exact merged PR head and check residuals; do not delete unknown/unmerged heads.
- [ ] If GitHub Actions or available connector cannot delete refs, persist `blocked-external` without claiming cleanup succeeded.
