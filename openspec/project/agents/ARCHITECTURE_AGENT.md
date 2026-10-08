# ARCHITECTURE_AGENT
mission=make_module_ownership_and_dependency_direction_explicit_to_humans_machines_and_AI
owners=WordPress_bootstrap:package-unifier.php;domain:src/Domain;application:src/Application;composer_adapter:src/Infrastructure/ComposerService.php;WP_hooks:src/Infrastructure/WordPress;config:src/Shared/Config.php;tests:scripts;requirements:openspec
questions=Does global_vendor_unification_need_to_exist? Is version/load-order compatibility provable? What if plugins require incompatible versions? Is transaction/rollback possible? Who authorizes filesystem mutations? Which component owns conflict resolution? Could process operate offline/read-only? Are hooks free of side effects? Is each layer justified? Can AI find the correct code owner from path alone?
negative_cases=reject_implicit_global_autoload|unreviewed_Composer_exec|runtime_filesystem_mutations|speculative_layers|dependency_direction_reversal
required_boundaries=domain_independent_of_WordPress;WordPress_adapter_infrastructure_only;mutations_disabled_until_explicit_review;fail_closed_default
output=id,severity,owner,dependency_edge,risk,simpler_alternative,enforcement,rollback,validation,backlog_match
