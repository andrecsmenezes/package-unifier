from __future__ import annotations

import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SOURCE = ROOT / "src"
ENTRY = ROOT / "package-unifier.php"

def php_code(path: Path) -> str:
    # Strip comments without discarding executable code after strings containing // or #.
    text = path.read_text(encoding="utf-8")
    out: list[str] = []
    i = 0
    quote: str | None = None
    while i < len(text):
        char = text[i]
        next_char = text[i + 1] if i + 1 < len(text) else ""
        if quote is not None:
            out.append(char)
            if char == "\\" and next_char:
                out.append(next_char)
                i += 2
                continue
            if char == quote:
                quote = None
            i += 1
            continue
        if char in ("'", '"'):
            quote = char
            out.append(char)
            i += 1
            continue
        if char == "/" and next_char == "*":
            end = text.find("*/", i + 2)
            comment_end = len(text) if end < 0 else end + 2
        elif (char == "/" and next_char == "/") or (
            char == "#" and next_char != "["
        ):
            end = text.find("\n", i)
            comment_end = len(text) if end < 0 else end
        else:
            out.append(char)
            i += 1
            continue
        out.extend("\n" if c == "\n" else " " for c in text[i:comment_end])
        i = comment_end
    return "".join(out)


def reachable_files() -> set[Path]:
    files = {path.stem: path for path in SOURCE.rglob("*.php")}
    reached = {ENTRY}
    pending = [ENTRY]
    while pending:
        current = pending.pop()
        code = php_code(current)
        for name, target in files.items():
            if target in reached:
                continue
            if re.search(r"(?<![\w])" + re.escape(name) + r"(?=\s*::|\s*\(|\s*;|\\)", code):
                reached.add(target)
                pending.append(target)
    return reached

FORBIDDEN = re.compile(
    r"\b(?:exec|shell_exec|system|passthru|proc_open|popen)\s*\("
    r"|\b(?:installDependencies|movePackages|installFromComposerFile|updateAutoloader|scan)\s*\("
    r"|\b(?:mkdir|rename|unlink|copy|file_put_contents)\s*\("
    r"|\b(?:call_user_func(?:_array)?|forward_static_call(?:_array)?|eval|assert)\s*\("
    r"|\b(?:include|require)(?:_once)?\b(?!\s*\$localAutoload\s*;)"
    r"|\bnew\s+\$[A-Za-z_]\w*"
    r"|\$[A-Za-z_]\w*\s*\(",
    re.I,
)

reached = reachable_files()
required = {
    ENTRY,
    SOURCE / "Infrastructure/WordPress/PluginActivator.php",
    SOURCE / "Infrastructure/WordPress/Hooks.php",
    SOURCE / "Shared/Config.php",
}
if not required.issubset(reached):
    raise SystemExit("REACHABLE_MUTATION=FAIL missing bootstrap ownership")
failures = [
    f"{path.relative_to(ROOT)}: {match.group(0)}"
    for path in sorted(reached)
    for match in FORBIDDEN.finditer(php_code(path))
]
if failures:
    raise SystemExit("REACHABLE_MUTATION=FAIL " + "; ".join(failures))
print(f"REACHABLE_MUTATION=READY files={len(reached)}")
