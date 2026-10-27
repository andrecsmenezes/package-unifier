import json
import tempfile
import unittest
from pathlib import Path

from scripts.cleanup_merged_branches import eligible, load_deletion_intents


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


if __name__ == "__main__":
    unittest.main()
