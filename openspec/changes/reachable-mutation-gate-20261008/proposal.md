# Proposal

problem=bootstrap safety currently uses fixed string checks; future reachable class references may activate mutation sinks without violating fixed-file checks
scope=dependency-free source reachability scan; block reachable shell/filesystem/Composer mutators; retain experimental disconnected classes
non_scope=dynamic PHP execution modeling, WordPress runtime integration, deletion of experimental classes
