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

# Run inside WordPress, not the mocked hook fixture; fail for unexpected first-party callbacks.
assert_plugin_callbacks() {
  wp-env run cli wp eval '
    $expected = [
      "plugins_loaded|PackageUnifier\\Infrastructure\\WordPress\\Hooks::loadTextDomain",
      "activate_package-unifier/package-unifier.php|PackageUnifier\\Infrastructure\\WordPress\\PluginActivator::activate",
      "deactivate_package-unifier/package-unifier.php|PackageUnifier\\Infrastructure\\WordPress\\PluginActivator::deactivate",
    ];
    $observed = [];
    foreach ($GLOBALS["wp_filter"] as $hook => $registration) {
      foreach ($registration->callbacks as $callbacks) {
        foreach ($callbacks as $entry) {
          $callback = $entry["function"];
          if (is_array($callback) && count($callback) === 2) {
            [$owner, $method] = $callback;
            $owner = is_object($owner) ? get_class($owner) : $owner;
            if (is_string($owner) && str_starts_with($owner, "PackageUnifier\\")) {
              $observed[] = $hook . "|" . $owner . "::" . $method;
            }
          } elseif (is_string($callback) && str_starts_with($callback, "PackageUnifier\\")) {
            $observed[] = $hook . "|" . $callback;
          }
        }
      }
    }
    sort($expected);
    sort($observed);
    if ($expected !== $observed) {
      throw new RuntimeException("Unexpected PackageUnifier hook registrations: " . json_encode($observed));
    }
  '
}


# Normal activation must register the actual plugin bootstrap without a fatal.
wp-env run cli wp plugin deactivate package-unifier || true
wp-env run cli wp plugin activate package-unifier
wp-env run cli wp plugin is-active package-unifier
wp-env run cli wp eval 'if (!class_exists("PackageUnifier\\Infrastructure\\WordPress\\PluginActivator")) { throw new RuntimeException("Plugin activation class missing."); }'
assert_plugin_callbacks
curl --fail --silent --show-error --retry 3 --max-time 20 http://127.0.0.1:8888/ >/dev/null

# Missing local dependencies must not fatal on activation, CLI or public HTTP.
wp-env run cli wp plugin deactivate package-unifier
mv "$root/vendor" "$held_vendor"
wp-env run cli wp plugin activate package-unifier
wp-env run cli wp plugin is-active package-unifier
wp-env run cli wp eval 'echo "WORDPRESS_MISSING_VENDOR=READY\n";'
wp-env run cli wp eval 'if (has_action("plugins_loaded", ["PackageUnifier\\Infrastructure\\WordPress\\Hooks", "loadTextDomain"]) !== false) { throw new RuntimeException("Plugin callback registered without local autoload."); }'
curl --fail --silent --show-error --retry 3 --max-time 20 http://127.0.0.1:8888/ >/dev/null

# Recover local autoload and assert the plugin still boots.
restore_vendor
wp-env run cli wp plugin deactivate package-unifier
wp-env run cli wp plugin activate package-unifier
wp-env run cli wp eval 'if (!class_exists("PackageUnifier\\Infrastructure\\WordPress\\PluginActivator")) { throw new RuntimeException("Local autoload recovery failed."); }'
assert_plugin_callbacks
echo "WORDPRESS_INTEGRATION=READY"
