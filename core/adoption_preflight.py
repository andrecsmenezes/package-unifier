"""Fail-closed OpenSpec adoption checks before publishing an archive migration PR."""
from hashlib import sha256
import json
from pathlib import Path
import re
import shutil
import subprocess

PURPOSE = re.compile(r"^## Purpose\s*$", re.MULTILINE)
REQUIREMENTS = re.compile(r"^## Requirements\s*$", re.MULTILINE)
REQUIREMENT = re.compile(r"^### Requirement: \S", re.MULTILINE)
OFFICIAL_ARGS = ("validate", "--all", "--strict")


class PreflightError(ValueError):
    """The candidate migration has not passed pre-PR official validation."""


def read_specs(root):
    """Read current canonical specs only; historical archives are inert."""
    root = Path(root).resolve()
    folder = root / "openspec" / "specs"
    files = sorted(folder.glob("*/spec.md"))
    if not files:
        raise PreflightError("No canonical OpenSpec specifications found")
    return {p.relative_to(root).as_posix(): p.read_text(encoding="utf-8") for p in files}


def structural_errors(specs):
    """Cheap early rejection; not a substitute for the official OpenSpec CLI."""
    if not specs:
        return ["No canonical OpenSpec specifications found"]
    errors = []
    for path, content in sorted(specs.items()):
        if not path.startswith("openspec/specs/") or not path.endswith("/spec.md"):
            errors.append(path + ": not a canonical spec path")
            continue
        if not PURPOSE.search(content):
            errors.append(path + ": missing ## Purpose")
        if not REQUIREMENTS.search(content):
            errors.append(path + ": missing ## Requirements")
        if not REQUIREMENT.search(content):
            errors.append(path + ": missing ### Requirement")
        if PURPOSE.search(content) and REQUIREMENTS.search(content) and PURPOSE.search(content).start() > REQUIREMENTS.search(content).start():
            errors.append(path + ": Purpose must precede Requirements")
    return errors


def spec_digest(specs):
    """Fingerprint reviewed preflight input for comparison with the proposed PR tree."""
    canonical = json.dumps(specs, sort_keys=True, ensure_ascii=False, separators=(",", ":"))
    return sha256(canonical.encode("utf-8")).hexdigest()


def run_adoption_preflight(root, *, cli="openspec", runner=subprocess.run):
    """Require both structural checks and the actual official CLI against candidate files.

    Call before opening a migration PR. Unavailable tools, malformed output and nonzero
    validation exit codes block PR creation; never substitute a different SHA's CI.
    """
    root = Path(root).resolve()
    specs = read_specs(root)
    issues = structural_errors(specs)
    if issues:
        raise PreflightError("; ".join(issues))
    executable = str(cli)
    candidate = Path(executable)
    if not candidate.is_absolute() and "/" in executable:
        candidate = root / candidate
        executable = str(candidate)
    if not (candidate.is_file() or shutil.which(executable)):
        raise PreflightError("Official OpenSpec CLI unavailable; migration preflight blocked")
    try:
        version = runner([executable, "--version"], cwd=str(root), capture_output=True, text=True, timeout=120)
        if version.returncode or not version.stdout.strip():
            raise PreflightError("Official OpenSpec CLI version verification failed")
        result = runner([executable, *OFFICIAL_ARGS], cwd=str(root), capture_output=True, text=True, timeout=300)
    except (OSError, subprocess.TimeoutExpired) as exc:
        raise PreflightError("Official OpenSpec CLI could not complete") from exc
    if result.returncode:
        excerpt = (result.stdout + "\n" + result.stderr).strip()[-2500:]
        raise PreflightError("Official OpenSpec strict validation failed: " + excerpt)
    return {"status": "PASS", "spec_digest": spec_digest(specs),
            "spec_count": len(specs), "cli_version": version.stdout.strip(),
            "command": "openspec validate --all --strict"}


def verify_preflight_input(root, proof):
    """Reject stale or missing CLI evidence before writing/opening the PR."""
    if not isinstance(proof, dict) or proof.get("status") != "PASS" or proof.get("command") != "openspec validate --all --strict":
        raise PreflightError("Missing official CLI preflight evidence")
    current = read_specs(root)
    if structural_errors(current) or proof.get("spec_digest") != spec_digest(current):
        raise PreflightError("OpenSpec source changed after preflight")
    if proof.get("spec_count") != len(current) or not proof.get("cli_version"):
        raise PreflightError("Incomplete OpenSpec preflight evidence")
    return True
