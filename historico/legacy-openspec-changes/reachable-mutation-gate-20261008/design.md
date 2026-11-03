# Design

entry=package-unifier.php;graph=referenced PHP class stems under src/;edges=static class reference detection;sinks=process execution, Composer mutation, filesystem mutation;fail=nonzero + path/sink;ci=runtime-safety
constraint=conservative static guard;not proof of runtime safety;dynamic_include_except_plugin_local_autoload_and_dynamic_callable_dispatch=forbidden_in_reachable_code;negative_fixtures=scripts/test_reachable_mutation.py;runtime_WordPress_integration=pending
