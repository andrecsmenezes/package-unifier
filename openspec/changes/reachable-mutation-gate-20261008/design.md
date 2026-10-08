# Design

entry=package-unifier.php;graph=referenced PHP class stems under src/;edges=static class reference detection;sinks=process execution, Composer mutation, filesystem mutation;fail=nonzero + path/sink;ci=runtime-safety
constraint=conservative static guard;not proof of runtime safety;dynamic references/includes remain pending integration coverage
