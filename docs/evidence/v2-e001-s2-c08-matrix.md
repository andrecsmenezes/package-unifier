# E-PU-V2-001 / S2 — Bounded reachable mutation evidence (C08)

Approved scope digest: 23662884e2286b7e2e46bdec6e0ade4a29b7c0c450d0d582211ae22272ab908f
Source baseline: 9116d997157c91c4d0b9746faf8beaa6fc243247
Execution scope: first-party bootstrap-reachable PHP and named WordPress hook registration only.
Validator: scripts/validate_reachable_mutation.py
Regression file: scripts/test_reachable_mutation.py
Current registered test methods in candidate: 50
Status: candidate awaiting exact-head GitHub CI; not a general PHP parser.

## Reachability ownership

Verified first-party bootstrap source ownership includes:

- package-unifier.php — ABSPATH gate, local-only Composer autoload, activation hook registration.
- src/Infrastructure/WordPress/PluginActivator.php — activation guard, deactivation and hook initialization.
- src/Infrastructure/WordPress/Hooks.php — only named plugins_loaded text-domain callback.
- src/Shared/Config.php — first-party local paths, constants and text domain.

The source-only guard discovers additional statically named classes reachable through these
files by conservative class-name matching. Unreachable PHP is not automatically included
in this scan and is not claimed secure by this result.

## Bounded negative and benign cases

| Case | Expected source guard behavior | Evidence |
| --- | --- | --- |
| Executable shell/file mutation | Reject | Existing exec, backticks, filesystem sink fixtures. |
| Direct or parenthesized callback invocation | Reject | Existing dynamic variable/literal/receiver tests. |
| Callback helper outside WordPress hooks | Reject | New call_user_func_array, forward_static_call and forward_static_call_array fixtures. |
| Reflection invocation and getClosure | Reject | Existing reflection fixtures plus new getClosure fixture. |
| Dynamic include or require outside one owned local autoload | Reject | Existing include directive matrix and exact owner rule. |
| Statically named non-hook helper reached through Hooks | Reject reachable exec sink | New transitive Helper::run fixture. |
| Callback-like text inside a quoted PHP string | Permit without false positive | New benign quoted-text fixture; source guard excludes quoted data from FORBIDDEN regex. |
| Real callback after benign literal text | Reject | New quoted-text-plus-invocation fixture. |
| First-party named/Closure/invokable registered WordPress hooks | Permit only known registrations | Existing scripts/test_wordpress_integration.sh; workflow now triggers for C08 guard/test changes. |

## Verified limitations

This is not a complete PHP parser or a guarantee about dynamic include targets,
runtime-computed class loading, reflection dispatch hidden behind arbitrary user
callables, non-hook dynamic dispatch patterns not represented by a fixture,
heredoc/nowdoc contexts, external vendor code or WordPress plugin ecosystem-wide
execution. The conservative validator may produce false positives or false negatives
outside its bounded source and syntax set.

No Composer invocation, live installed-plugin scanning, global vendor autoload,
vendor directory write or production deployment is introduced.

## Acceptance

- GitHub PR-head runtime-safety and regression tests PASS.
- Real WordPress integration hook-registry tests PASS on the PR head.
- Official OpenSpec strict validation and independent dependency security PASS.
- Branch cleanup after verified main merge, no residual temporary ref.
