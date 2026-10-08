import unittest

from scripts.cleanup_merged_branches import eligible


def merged(branch="feat/x", sha="abc", base="main"):
    return [{
        "state": "closed",
        "merged_at": "2026-10-08T00:00:00Z",
        "head": {"ref": branch, "sha": sha},
        "base": {"ref": base},
    }]


class BranchCleanupTest(unittest.TestCase):
    def test_exact_merged_head_can_be_deleted(self):
        self.assertTrue(eligible("feat/x", "abc", "main", merged(), []))

    def test_default_branch_cannot_be_deleted(self):
        self.assertFalse(eligible("main", "abc", "main", merged("main"), []))

    def test_reused_branch_head_is_preserved(self):
        self.assertFalse(eligible("feat/x", "new", "main", merged(), []))

    def test_open_pull_request_preserves_branch(self):
        self.assertFalse(eligible("feat/x", "abc", "main", merged(), [{"number": 2}]))

    def test_unmerged_or_wrong_base_is_preserved(self):
        self.assertFalse(eligible("feat/x", "abc", "main", [dict(merged()[0], merged_at=None)], []))
        self.assertFalse(eligible("feat/x", "abc", "main", merged(base="staging"), []))

    def test_unknown_branch_cannot_be_deleted(self):
        self.assertFalse(eligible("feat/x", "abc", "main", [], []))


if __name__ == "__main__":
    unittest.main()
