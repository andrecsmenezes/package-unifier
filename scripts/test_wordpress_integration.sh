#!/usr/bin/env bash
set -euo pipefail

root="$(cd "$(dirname "$0")/.." && pwd)"
cd "$root"
held_vendor="$root/vendor.integration-disabled"

restore_vendor() {
  if [[ -d "$held_vendor" && ! -e "$root/vendor" ]]; then
    mv "$held_vendor" "$root/vendor"
  fi
}
trap restore_vendor EXIT

test -d "$root/vendor"
test ! -e "$held_vendor"
wp-env run cli wp core is-installed

# Normal activation must register the actual plugin bootstrap without a fatal.
wp-env run cli wp plugin deactivate package-unifier || true
wp-env run cli wp plugin activate package-unifier
wp-env run cli wp plugin is-active package-unifier
wp-env run cli wp eval 'if (!class_exists("PackageUnifier\\Infrastructure\\WordPress\\PluginActivator")) { throw new RuntimeException("Plugin activation class missing."); }'
curl --fail --silent --show-error --retry 3 --max-time 20 http://127.0.0.1:8888/ >/dev/null

# Missing local dependencies must not fatal on activation, CLI or public HTTP.
wp-env run cli wp plugin deactivate package-unifier
mv "$root/vendor" "$held_vendor"
wp-env run cli wp plugin activate package-unifier
wp-env run cli wp plugin is-active package-unifier
wp-env run cli wp eval 'echo "WORDPRESS_MISSING_VENDOR=READY\n";'
curl --fail --silent --show-error --retry 3 --max-time 20 http://127.0.0.1:8888/ >/dev/null

# Recover local autoload and assert the plugin still boots.
restore_vendor
wp-env run cli wp plugin deactivate package-unifier
wp-env run cli wp plugin activate package-unifier
wp-env run cli wp eval 'if (!class_exists("PackageUnifier\\Infrastructure\\WordPress\\PluginActivator")) { throw new RuntimeException("Local autoload recovery failed."); }'
echo "WORDPRESS_INTEGRATION=READY"
