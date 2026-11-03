# Canonical OpenSpec backlog
schema=backlog.v2;owner=openspec/BACKLOG.md;state=DONE|OPEN;priority=P0_SECURITY|P0_OPENSPEC|P1_CODE|P1_I18N|P2_DECISION|LIFECYCLE;record=ID|STATE|ACTION_OR_EVIDENCE
rules=English_only;AI_first;source_of_truth=OpenSpec;dedup_findings;adversarial_agent_review;highest_severity_first;merge_main_after_each_stage;delete_completed_temporary_refs;never_invent_work
P0_SECURITY:
S01|DONE|WordPress bootstrap fails closed; only plugin-local autoload; no Composer mutations during request/activation.
S02|DONE|Deleted unauthenticated export.php and generated php_files_report.txt; regression guard added.
S03|DONE|Runtime-safety workflow observed passing on main after source-export removal PR #2.
S04|DONE|PHP WordPress-function bootstrap fixture covers absent/local vendor, admin-only notice, hooks, activation/deactivation, gettext; fixture is NOT real WordPress integration.
S05|DONE|Real @wordpress/env integration covers activation, missing vendor, public HTTP no fatal, dependency recovery. PR #11 CI 37857648046 PASS; main CI 37857932855 PASS; runtime-safety 37857932844 PASS; OpenSpec 37857932859 PASS; cleanup 37857932875 PASS; branches=main only.
S06|OPEN|After deploying updated main, verify legacy deployments no longer expose source-export files; external deployment evidence required.
P0_OPENSPEC:
O01|DONE|Official OpenSpec config and CLI init/list/strict-validation CI workflow installed.
O02|DONE|CODE_DEDUP_AGENT, CLEAN_CODE_AGENT, ARCHITECTURE_AGENT, I18N_AGENT contracts registered; adversarial questions and bounded ownership.
O03|DONE|Observed official OpenSpec initialization and strict validation passing; prior run evidence only, not current-head parser proof.
O04|OPEN|Commit generated tool integration ONLY after actual openspec init/update; do not handcraft generated skills.
O05|OPEN|Sync/archive bootstrap-safety change ONLY after all official OpenSpec and runtime checks succeed.
P1_CODE:
C01|DONE|Confirmed scanner, installer, updater, Composer adapter unreachable from bootstrap; removed six unused experimental classes; runtime regression still required.
C02|OPEN|Before new dependency consolidation, design reviewed CLI-only owner, correct package-constraint resolution, version-conflict isolation, autoload order, rollback and concurrency tests; never reenable runtime Composer mutations.
C03|OPEN|Remove committed vendor/ ONLY after composer install reproducibility and deployment/artifact strategy are verified; preserve local-autoload WordPress boot.
C04|DONE|Removed human-oriented docblocks from dormant models, adapters, config; PHP tokens and safety invariants preserved; PR validation still required.
C05|DONE|Added conservative bootstrap entrypoint-reachability no-mutation static gate for first-party code, shell/filesystem/Composer sinks.
C06|DONE|Added negative fixtures rejecting dynamic PHP functions, callbacks, class dispatch and unapproved includes in bootstrap-reachable source.
C07|DONE|Fixed comment lexer URL/hash/block delimiter confusion in strings; negative and benign cases in scripts/test_reachable_mutation.py.
C08|OPEN|Real WordPress hook-registry regression inspects first-party named invokable objects and source-owned Closure callbacks; PR #21 established baseline. Dynamic includes, reflective dispatch and non-hook callable paths remain NOT_PROVEN; reachable shell-backtick lexical guard and negative fixtures require exact-head CI; no general PHP parser/runtime safety claim.
P1_I18N:
I01|DONE|Single admin notice uses esc_html__ with text domain package-unifier and pt-BR source; en/es translations require reviewed need.
I02|DONE|Active bootstrap audit: package-unifier.php; src/Infrastructure/WordPress/{Hooks,PluginActivator}.php; src/Shared/Config.php. No reachable date/time/number/currency/plural formatter; notice uses WordPress esc_html__ and pt-BR source. scripts/test_wordpress_bootstrap.php covers notice/domain. Scope=first-party static+fixture, NOT live locale certification; excludes vendor/ and unshipped code; reaudit new output; technical docs English only.
P2_DECISION:
D01|OPEN|NEED=UNVERIFIED(no measured cross-plugin Composer conflict or approved consolidation requirement); A=KEEP plugin-local vendor/autoload.php(default; existing fail-closed runtime); B=READ_ONLY offline comparison limited to explicitly provided manifest/lock metadata(no live WordPress/plugin scan or secrets); C=CLI_ONLY sandboxed constraint/autoload compatibility evaluation after named-owner approval(no production vendor/filesystem mutations); GLOBAL_RUNTIME_COALESCING=PROHIBITED; selection requires observed conflict, approved owner, version/semver matrix, isolation, authorization and rollback per D02/C02. Source=README.md+composer.json+current bootstrap; this is a bounded decision comparison, not operational validation.
D02|OPEN|Before ANY Composer/vendor mutation require plugin compatibility, semver conflict matrix, authorization, sandbox, atomic transactions, concurrency, backup, rollback, integration tests.
D03|OPEN|2026-10-28_SUPPORT_SNAPSHOT: WordPress recommends PHP>=8.3; WP7.0+ permits legacy PHP>=7.4 but EOL versions are unsafe. PHP security support: 8.2 until 2026-12-31;8.3 until 2027-12-31;8.4 until 2028-12-31;8.5 until 2029-12-31. Current composer.json declares php:^8.0 and composer/installers:^2.3; published composer/installers v2.3.0 requires php:^7.2||^8.0 and composer-plugin-api:^1||^2. These are metadata compatibilities, NOT proof of current runtime support. Deployed WordPress/PHP/Composer, lock resolution and integration matrix UNVERIFIED; no automatic minimum-version bump/deploy. Next=verify deployed exact versions, WordPress integration, Composer lock and policy owner before production. Sources=https://wordpress.org/about/requirements/,https://make.wordpress.org/core/2026/05/22/php-support-clarification-2026/,https://www.php.net/supported-versions.php,https://packagist.org/packages/composer/installers.
LIFECYCLE:
L01|DONE|Merged-PR-head cleanup after main push includes exact head, no-open-PR guard and unit tests.
L02|DONE|Observed hosted cleanup deleting completed temporary refs.
L03|DONE|Deleted merged PR #3 branch chore/openspec-agents-bootstrap-20261008 after exact head verification.
L04|DONE|Deleted merged cleanup PR #4 branch after merge using exact-head guard.
L05|DONE|Deleted merged PR #1 branch fix/failclosed-wordpress-bootstrap-20261007 after exact head verification.
L06|DONE|Deleted merged PR #2 branch fix/remove-public-code-exporter-20261008 after exact head verification.
L07|OPEN|For each new merged change, delete exact registered merged PR head and scan residuals; never remove unknown/unmerged heads.
L08|OPEN|When Actions or connector cannot delete refs, persist blocked-external instead of claiming cleanup.
historical_2026-10-08=main_runtime_safety_37729851358:PASS;main_openspec_37729851307:PASS;main_cleanup_37729851304:PASS;branch_inventory:main_only;PR5_merged:3757f2f20632f6a8381a6d8116ba53734b2acc21
