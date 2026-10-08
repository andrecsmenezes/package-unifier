from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
entry = (ROOT / 'package-unifier.php').read_text(encoding='utf-8')
hooks = (ROOT / 'src/Infrastructure/WordPress/Hooks.php').read_text(encoding='utf-8')
activator = (ROOT / 'src/Infrastructure/WordPress/PluginActivator.php').read_text(encoding='utf-8')
config = (ROOT / 'src/Shared/Config.php').read_text(encoding='utf-8')

checks = {
    'explicit WordPress guard': "defined('ABSPATH')" in entry,
    'local autoloader guard': "is_file($localAutoload)" in entry,
    'local autoloader load': "require_once $localAutoload;" in entry,
    'no global autoloader in entrypoint': 'GLOBAL_AUTOLOAD' not in entry and 'GLOBAL_VENDOR' not in entry,
    'no request-triggered vendor scan': "add_action('init'" not in hooks and 'scanPlugins' not in hooks,
    'localization-only lifecycle': "add_action('plugins_loaded'" in hooks,
    'no activation-time global mutations': 'mkdir(' not in activator and 'exec(' not in activator and 'installDependencies(' not in activator,
    'correct plugin root': "ROOT_DIR = __DIR__ . '/../../'" in config,
}
failed = [name for name, ok in checks.items() if not ok]
if failed:
    raise SystemExit('BOOTSTRAP_SAFETY=FAIL ' + ','.join(failed))
print('BOOTSTRAP_SAFETY=READY checks=' + str(len(checks)))
