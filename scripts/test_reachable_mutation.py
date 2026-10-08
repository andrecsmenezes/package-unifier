from __future__ import annotations

import subprocess
import sys
import tempfile
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


class ReachableMutationGuardTest(unittest.TestCase):
    def setUp(self) -> None:
        self.temp = tempfile.TemporaryDirectory()
        self.addCleanup(self.temp.cleanup)
        self.root = Path(self.temp.name)
        (self.root / "scripts").mkdir()
        (self.root / "src/Infrastructure/WordPress").mkdir(parents=True)
        (self.root / "src/Shared").mkdir(parents=True)
        (self.root / "src/Infrastructure").mkdir(parents=True, exist_ok=True)
        (self.root / "scripts/validate_reachable_mutation.py").write_text(
            (ROOT / "scripts/validate_reachable_mutation.py").read_text(encoding="utf-8"),
            encoding="utf-8",
        )
        (self.root / "package-unifier.php").write_text(
            "<?php PluginActivator::activate(); Hooks::register(); Config::TEXT_DOMAIN; "
            "require_once $localAutoload;",
            encoding="utf-8",
        )
        (self.root / "src/Infrastructure/WordPress/PluginActivator.php").write_text(
            "<?php class PluginActivator {}", encoding="utf-8"
        )
        self.hooks = self.root / "src/Infrastructure/WordPress/Hooks.php"
        self.hooks.write_text("<?php class Hooks {}", encoding="utf-8")
        (self.root / "src/Shared/Config.php").write_text(
            "<?php class Config {}", encoding="utf-8"
        )

    def assert_gate(self, passed: bool) -> None:
        run = subprocess.run(
            [sys.executable, str(self.root / "scripts/validate_reachable_mutation.py")],
            capture_output=True,
            text=True,
            check=False,
            timeout=20,
        )
        combined = run.stdout + run.stderr
        if passed:
            self.assertEqual(run.returncode, 0, combined)
            self.assertIn("REACHABLE_MUTATION=READY", combined)
        else:
            self.assertNotEqual(run.returncode, 0, combined)
            self.assertIn("REACHABLE_MUTATION=FAIL", combined)

    def test_safe_bootstrap_local_autoload(self) -> None:
        self.assert_gate(True)

    def test_unreachable_research_sink_is_not_runtime_reachable(self) -> None:
        (self.root / "src/Infrastructure/ComposerService.php").write_text(
            '<?php class ComposerService { public function mutate() { exec("id"); } }',
            encoding="utf-8",
        )
        self.assert_gate(True)

    def test_reachable_shell_sink_is_rejected(self) -> None:
        self.hooks.write_text('<?php class Hooks { public function f() { exec("id"); } }')
        self.assert_gate(False)

    def test_dynamic_function_dispatch_is_rejected(self) -> None:
        self.hooks.write_text(
            '<?php class Hooks { public function f() { $callback("id"); } }'
        )
        self.assert_gate(False)

    def test_dynamic_callback_helper_is_rejected(self) -> None:
        self.hooks.write_text(
            "<?php class Hooks { public function f() { call_user_func($callback); } }"
        )
        self.assert_gate(False)

    def test_unapproved_include_is_rejected(self) -> None:
        self.hooks.write_text(
            "<?php class Hooks { public function f() { require $path; } }"
        )
        self.assert_gate(False)

    def test_dynamic_class_is_rejected(self) -> None:
        self.hooks.write_text("<?php class Hooks { public function f() { new $class; } }")
        self.assert_gate(False)


if __name__ == "__main__":
    unittest.main()
