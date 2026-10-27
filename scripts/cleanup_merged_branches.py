from __future__ import annotations

import json
import os
import urllib.error
import urllib.parse
import urllib.request
from pathlib import Path


INTENT_DISPOSITION = "DELETE_AFTER_VERIFIED_MAIN_MERGE"


def load_deletion_intents(path: Path, repository: str) -> set[str]:
    """Fail closed on an ambiguous or foreign branch deletion authorization."""
    data = json.loads(path.read_text(encoding="utf-8"))
    if not isinstance(data, dict) or data.get("version") != 1 or data.get("owner") != repository:
        raise RuntimeError("Invalid deletion register version or repository owner")
    entries = data.get("deletion_intents")
    if not isinstance(entries, list):
        raise RuntimeError("Invalid deletion register entries")
    registered: set[str] = set()
    work_units: set[str] = set()
    for entry in entries:
        if not isinstance(entry, dict):
            raise RuntimeError("Invalid deletion register entry")
        branch = entry.get("branch")
        unit = entry.get("work_unit_id")
        if (
            not isinstance(branch, str) or not branch.strip() or branch != branch.strip()
            or branch in registered
            or not isinstance(unit, str) or not unit.strip() or unit in work_units
            or entry.get("disposition") != INTENT_DISPOSITION
        ):
            raise RuntimeError("Ambiguous or unauthorized deletion intent")
        registered.add(branch)
        work_units.add(unit)
    return registered


def eligible(
    branch: str, sha: str, default: str, merged: list[dict],
    open_prs: list[dict], registered: set[str],
) -> bool:
    if not branch or not sha or branch == default or branch not in registered or open_prs:
        return False
    return any(
        pr.get("merged_at")
        and pr.get("state") == "closed"
        and (pr.get("head") or {}).get("ref") == branch
        and (pr.get("head") or {}).get("sha") == sha
        and (pr.get("base") or {}).get("ref") == default
        for pr in merged
    )


class GitHub:
    def __init__(self) -> None:
        self.repo = os.environ["GH_REPOSITORY"]
        self.owner = self.repo.split("/", 1)[0]
        self.default = os.environ["DEFAULT_BRANCH"]
        self.headers = {
            "Accept": "application/vnd.github+json",
            "Authorization": "Bearer " + os.environ["GH_TOKEN"],
            "X-GitHub-Api-Version": "2022-11-28",
            "User-Agent": "package-unifier-safe-cleanup",
        }

    def api(self, path: str, method: str = "GET", allow_404: bool = False):
        req = urllib.request.Request("https://api.github.com" + path, headers=self.headers, method=method)
        try:
            with urllib.request.urlopen(req, timeout=30) as response:
                raw = response.read()
                return json.loads(raw) if raw else None
        except urllib.error.HTTPError as error:
            if allow_404 and error.code == 404:
                return None
            raise RuntimeError(f"{method} {path}: HTTP {error.code}") from error

    def list_all(self, path: str) -> list[dict]:
        out: list[dict] = []
        page = 1
        while True:
            separator = "&" if "?" in path else "?"
            batch = self.api(f"{path}{separator}per_page=100&page={page}") or []
            out.extend(batch)
            if len(batch) < 100:
                return out
            page += 1

    def run(self) -> tuple[list[str], list[str]]:
        deleted, kept = [], []
        registered = load_deletion_intents(
            Path(__file__).resolve().parents[1] / "orchestration/branch-deletion-register.json",
            self.repo,
        )
        for item in self.list_all(f"/repos/{self.repo}/branches"):
            branch = item["name"]
            sha = (item.get("commit") or {}).get("sha", "")
            if branch == self.default:
                continue
            query = urllib.parse.urlencode({"state": "closed", "base": self.default, "head": f"{self.owner}:{branch}"})
            merged = self.list_all(f"/repos/{self.repo}/pulls?{query}")
            open_query = urllib.parse.urlencode({"state": "open", "head": f"{self.owner}:{branch}"})
            open_prs = self.list_all(f"/repos/{self.repo}/pulls?{open_query}")
            if not eligible(branch, sha, self.default, merged, open_prs, registered):
                kept.append(branch)
                continue
            encoded = urllib.parse.quote(branch, safe="/")
            self.api(f"/repos/{self.repo}/git/refs/heads/{encoded}", method="DELETE")
            verify = urllib.parse.quote(branch, safe="")
            if self.api(f"/repos/{self.repo}/branches/{verify}", allow_404=True) is not None:
                raise RuntimeError(f"GitHub did not confirm branch deletion: {branch}")
            deleted.append(branch)
        return deleted, kept


if __name__ == "__main__":
    deleted, preserved = GitHub().run()
    print("deleted_branches=" + ",".join(sorted(deleted)))
    print("preserved_branches=" + ",".join(sorted(preserved)))
    print("BRANCH_CLEANUP=READY")
