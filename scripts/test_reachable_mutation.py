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


    def test_reflection_method_invocation_is_rejected(self) -> None:
        self.hooks.write_text(
            '<?php class Hooks { public function f() { $r = new ReflectionMethod("Safe", "run"); $r->invoke($target); } }'
        )
        self.assert_gate(False)

    def test_reflection_instantiation_is_rejected(self) -> None:
        self.hooks.write_text(
            '<?php class Hooks { public function f() { $r = new ReflectionClass("Safe"); $r->newInstanceArgs([]); } }'
        )
        self.assert_gate(False)

    def test_callable_conversion_is_rejected(self) -> None:
        self.hooks.write_text(
            '<?php class Hooks { public function f() { \\Closure::fromCallable($target); } }'
        )
        self.assert_gate(False)

    def test_dynamic_braced_member_invocation_is_rejected(self) -> None:
        self.hooks.write_text(
            '<?php class Hooks { public function f() { $this->{$method}(); } }'
        )
        self.assert_gate(False)

    def test_reflection_metadata_only_remains_permitted(self) -> None:
        self.hooks.write_text(
            '<?php class Hooks { public function f() { $r = new ReflectionClass("Safe"); $r->getName(); } }'
        )
        self.assert_gate(True)

    def test_url_string_does_not_hide_reachable_shell_sink(self) -> None:
        self.hooks.write_text(
            '<?php class Hooks { public function f() { echo "https://example.test"; exec("id"); } }'
        )
        self.assert_gate(False)

    def test_hash_inside_php_string_does_not_hide_shell_sink(self) -> None:
        self.hooks.write_text(
            '<?php class Hooks { public function f() { echo "route#fragment"; exec("id"); } }'
        )
        self.assert_gate(False)

    def test_block_comment_marker_inside_string_does_not_hide_sink(self) -> None:
        self.hooks.write_text(
            '<?php class Hooks { public function f() { echo "/* text */"; exec("id"); } }'
        )
        self.assert_gate(False)

    def test_shell_execution_backtick_is_rejected(self) -> None:
        self.hooks.write_text(
            '<?php class Hooks { public function f() { $output = `id`; } }'
        )
        self.assert_gate(False)

    def test_backtick_text_inside_php_string_is_permitted(self) -> None:
        self.hooks.write_text(
            '<?php class Hooks { public function f() { echo "literal `code`"; } }'
        )
        self.assert_gate(True)

    def test_backtick_text_after_escaped_quote_is_permitted(self) -> None:
        self.hooks.write_text(
            r'''<?php class Hooks { public function f() { echo "escaped \" quote `code`"; } }'''
        )
        self.assert_gate(True)

    def test_backtick_in_php_comment_is_permitted(self) -> None:
        self.hooks.write_text(
            "<?php class Hooks { public function f() { // shell `id` ignored\n"
            "echo 'safe'; } }"
        )
        self.assert_gate(True)

    def test_actual_php_comments_are_ignored(self) -> None:
        self.hooks.write_text(
            "<?php class Hooks { public function f() { // exec('never')\n"
            "echo 'safe'; # exec('never')\n"
            "/* exec('never') */ } }"
        )
        self.assert_gate(True)

    def test_quote_escape_preserves_comment_delimiters_as_string(self) -> None:
        self.hooks.write_text(
            r'''<?php class Hooks { public function f() { echo "a\"https://example.test"; exec("id"); } }'''
        )
        self.assert_gate(False)


    def test_unbraced_object_method(self) -> None:
        self.hooks.write_text("<?php class Hooks { function f() { $this->$method(); } }")
        self.assert_gate(False)

    def test_unbraced_static_method(self) -> None:
        self.hooks.write_text("<?php class Hooks { function f() { Hooks::$method(); } }")
        self.assert_gate(False)


    def test_variable_variable_function_is_rejected(self) -> None:
        self.hooks.write_text(
            "<?php class Hooks { function f() { $callback('id'); } }"
        )
        self.assert_gate(False)

    def test_braced_variable_variable_function_is_rejected(self) -> None:
        self.hooks.write_text(
            "<?php class Hooks { function f() { ${$callback}('id'); } }"
        )
        self.assert_gate(False)

    def test_parenthesized_variable_function_is_rejected(self) -> None:
        self.hooks.write_text(
            "<?php class Hooks { function f() { ($callback)('id'); } }"
        )
        self.assert_gate(False)

    def test_literal_method(self) -> None:
        self.hooks.write_text("<?php class Hooks { function f() { $this->safe(); } }")
        self.assert_gate(True)

    def test_braced_static_dynamic_method_is_rejected(self) -> None:
        self.hooks.write_text("<?php class Hooks { function f() { Hooks::{$method}(); } }")
        self.assert_gate(False)

    def test_dollar_braced_static_dynamic_method_is_rejected(self) -> None:
        self.hooks.write_text("<?php class Hooks { function f() { Hooks::${method}(); } }")
        self.assert_gate(False)

    def test_literal_static_method_remains_permitted(self) -> None:
        self.hooks.write_text("<?php class Hooks { function f() { Hooks::safe(); } }")
        self.assert_gate(True)

    def test_parenthesized_literal_callable_is_rejected(self) -> None:
        self.hooks.write_text(
            "<?php class Hooks { function f() { ('system')('true'); } }"
        )
        self.assert_gate(False)

    def test_parenthesized_double_quoted_callable_is_rejected(self) -> None:
        self.hooks.write_text(
            '<?php class Hooks { function f() { ("system")("true"); } }'
        )
        self.assert_gate(False)

    def test_parenthesized_literal_callable_with_whitespace_is_rejected(self) -> None:
        self.hooks.write_text(
            "<?php class Hooks { function f() { (  'system'  ) \n ('true'); } }"
        )
        self.assert_gate(False)

    def test_parenthesized_literal_callable_inside_string_is_permitted(self) -> None:
        self.hooks.write_text(
            """<?php class Hooks { function f() { echo "text ('system')('true')"; } }"""
        )
        self.assert_gate(True)

    def test_parenthesized_literal_callable_inside_comment_is_permitted(self) -> None:
        self.hooks.write_text(
            "<?php class Hooks { function f() { // ('system')('true')\n echo 'safe'; } }"
        )
        self.assert_gate(True)

    def test_parenthesized_string_value_without_call_is_permitted(self) -> None:
        self.hooks.write_text(
            "<?php class Hooks { function f() { echo ('system'); } }"
        )
        self.assert_gate(True)



    def test_secondary_autoload_include_in_hooks_is_rejected(self) -> None:
        for directive in ("require_once", "require", "include", "include_once"):
            with self.subTest(directive=directive):
                self.hooks.write_text(
                    f"<?php class Hooks {{ function f() {{ {directive} $localAutoload; }} }}",
                    encoding="utf-8",
                )
                self.assert_gate(False)

    def test_entrypoint_alternative_include_keywords_are_rejected(self) -> None:
        entry = self.root / "package-unifier.php"
        original = entry.read_text(encoding="utf-8")
        for directive in ("require", "include", "include_once"):
            with self.subTest(directive=directive):
                entry.write_text(
                    original.replace(
                        "require_once $localAutoload;",
                        f"{directive} $localAutoload;",
                    ),
                    encoding="utf-8",
                )
                self.assert_gate(False)

    def test_entrypoint_duplicate_autoload_is_rejected(self) -> None:
        entry = self.root / "package-unifier.php"
        entry.write_text(
            entry.read_text(encoding="utf-8")
            + "\nrequire_once $localAutoload;\n",
            encoding="utf-8",
        )
        self.assert_gate(False)

    def test_quoted_include_statement_is_not_executed(self) -> None:
        self.hooks.write_text(
            '<?php class Hooks { function f() { echo "include $path;"; } }',
            encoding="utf-8",
        )
        self.assert_gate(True)


if __name__ == "__main__":
    unittest.main()
