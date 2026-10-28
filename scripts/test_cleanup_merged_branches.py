import json
import tempfile
import unittest
from pathlib import Path
from unittest.mock import patch

from scripts.cleanup_merged_branches import GitHub, current_ref_matches, eligible, load_deletion_intents


def merged(branch="feat/x", sha="abc", base="main"):
    return [{
        "state": "closed",
        "merged_at": "2026-10-08T00:00:00Z",
        "head": {"ref": branch, "sha": sha},
        "base": {"ref": base},
    }]


class BranchCleanupTest(unittest.TestCase):
    registered = {"feat/x"}

    def test_exact_merged_registered_head_can_be_deleted(self):
        self.assertTrue(eligible("feat/x", "abc", "main", merged(), [], self.registered))

    def test_unregistered_merged_head_cannot_be_deleted(self):
        self.assertFalse(eligible("feat/x", "abc", "main", merged(), [], set()))

    def test_default_branch_cannot_be_deleted(self):
        self.assertFalse(eligible("main", "abc", "main", merged("main"), [], {"main"}))

    def test_reused_branch_head_is_preserved(self):
        self.assertFalse(eligible("feat/x", "new", "main", merged(), [], self.registered))

    def test_open_pull_request_preserves_branch(self):
        self.assertFalse(eligible("feat/x", "abc", "main", merged(), [{"number": 2}], self.registered))

    def test_unmerged_or_wrong_base_is_preserved(self):
        self.assertFalse(eligible(
            "feat/x", "abc", "main", [dict(merged()[0], merged_at=None)], [], self.registered,
        ))
        self.assertFalse(eligible("feat/x", "abc", "main", merged(base="staging"), [], self.registered))

    def test_unknown_branch_cannot_be_deleted(self):
        self.assertFalse(eligible("feat/x", "abc", "main", [], [], self.registered))


class BranchDeletionRegisterTest(unittest.TestCase):
    repo = "andrecsmenezes/package-unifier"

    def intent(self, branch="feat/x", work_unit_id="UNIT-1", disposition="DELETE_AFTER_VERIFIED_MAIN_MERGE"):
        return {"branch": branch, "work_unit_id": work_unit_id, "disposition": disposition}

    def load(self, data, repo=None):
        with tempfile.TemporaryDirectory() as folder:
            path = Path(folder) / "branch-deletion-register.json"
            path.write_text(json.dumps(data), encoding="utf-8")
            return load_deletion_intents(path, repo or self.repo)

    def test_authorized_branches_are_loaded(self):
        self.assertEqual(self.load({
            "version": 1,
            "owner": self.repo,
            "deletion_intents": [self.intent(), self.intent("fix/second", "UNIT-2")],
        }), {"feat/x", "fix/second"})

    def test_wrong_owner_or_version_is_rejected(self):
        for override in ({"owner": "other/repo"}, {"version": 2}):
            data = {"version": 1, "owner": self.repo, "deletion_intents": [self.intent()]}
            data.update(override)
            with self.subTest(override=override), self.assertRaises(RuntimeError):
                self.load(data)

    def test_duplicate_branch_or_work_unit_is_rejected(self):
        for second in (self.intent(work_unit_id="UNIT-2"), self.intent("fix/y")):
            with self.subTest(second=second), self.assertRaises(RuntimeError):
                self.load({
                    "version": 1, "owner": self.repo,
                    "deletion_intents": [self.intent(), second],
                })

    def test_wrong_disposition_or_blank_identity_is_rejected(self):
        for intent in (self.intent(disposition="KEEP"), self.intent(branch=" "),
                       self.intent(work_unit_id=" "), self.intent(branch=" feat/x")):
            with self.subTest(intent=intent), self.assertRaises(RuntimeError):
                self.load({"version": 1, "owner": self.repo, "deletion_intents": [intent]})

    def test_missing_or_malformed_registry_prevents_deletion(self):
        with self.assertRaises(RuntimeError):
            self.load({"version": 1, "owner": self.repo, "deletion_intents": None})
        with tempfile.TemporaryDirectory() as folder:
            with self.assertRaises(FileNotFoundError):
                load_deletion_intents(Path(folder) / "absent.json", self.repo)



class CurrentRefTest(unittest.TestCase):
    def test_current_exact_commit_ref_is_accepted(self):
        ref = {"ref": "refs/heads/feat/x", "object": {"type": "commit", "sha": "abc"}}
        self.assertTrue(current_ref_matches(ref, "feat/x", "abc"))

    def test_moved_missing_or_malformed_refs_are_rejected(self):
        for ref in (
            None,
            {},
            {"ref": "refs/heads/feat/x", "object": {"type": "commit", "sha": "new"}},
            {"ref": "refs/heads/feat/y", "object": {"type": "commit", "sha": "abc"}},
            {"ref": "refs/heads/feat/x", "object": {"type": "tag", "sha": "abc"}},
            {"ref": "refs/heads/feat/x", "object": None},
        ):
            with self.subTest(ref=ref):
                self.assertFalse(current_ref_matches(ref, "feat/x", "abc"))
        self.assertFalse(current_ref_matches({"ref": "refs/heads/feat/x", "object": {"type": "commit", "sha": ""}}, "feat/x", ""))


class RefDeletionRecheckTest(unittest.TestCase):
    class FakeGitHub(GitHub):
        def __init__(self, remote_ref):
            self.repo = "andrecsmenezes/package-unifier"
            self.owner = "andrecsmenezes"
            self.default = "main"
            self.remote_ref = remote_ref
            self.calls = []

        def list_all(self, path):
            if path.endswith("/branches"):
                return [{"name": "feat/x", "commit": {"sha": "abc"}}]
            if "state=closed" in path:
                return merged()
            if "state=open" in path:
                return []
            raise AssertionError(path)

        def api(self, path, method="GET", allow_404=False):
            self.calls.append((method, path))
            if path.endswith("/git/ref/heads/feat/x") and method == "GET":
                return self.remote_ref
            if path.endswith("/git/refs/heads/feat/x") and method == "DELETE":
                return None
            if path.endswith("/branches/feat%2Fx") and method == "GET":
                return None
            raise AssertionError((method, path))

    def test_moved_head_is_retained_without_delete(self):
        changed = {"ref": "refs/heads/feat/x", "object": {"type": "commit", "sha": "new"}}
        api = self.FakeGitHub(changed)
        with patch("scripts.cleanup_merged_branches.load_deletion_intents", return_value={"feat/x"}):
            deleted, kept = api.run()
        self.assertEqual((deleted, kept), ([], ["feat/x"]))
        self.assertFalse(any(method == "DELETE" for method, _ in api.calls))

    def test_missing_head_is_retained_without_delete(self):
        api = self.FakeGitHub(None)
        with patch("scripts.cleanup_merged_branches.load_deletion_intents", return_value={"feat/x"}):
            deleted, kept = api.run()
        self.assertEqual((deleted, kept), ([], ["feat/x"]))
        self.assertFalse(any(method == "DELETE" for method, _ in api.calls))

    def test_matching_head_uses_existing_verified_delete_path(self):
        current = {"ref": "refs/heads/feat/x", "object": {"type": "commit", "sha": "abc"}}
        api = self.FakeGitHub(current)
        with patch("scripts.cleanup_merged_branches.load_deletion_intents", return_value={"feat/x"}):
            deleted, kept = api.run()
        self.assertEqual((deleted, kept), (["feat/x"], []))
        self.assertEqual(sum(method == "DELETE" for method, _ in api.calls), 1)


if __name__ == "__main__":
    unittest.main()
