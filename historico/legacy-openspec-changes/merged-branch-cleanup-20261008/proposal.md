# Proposal: exact merged-branch cleanup

## Why

Completed branches accumulate because a successful PR merge does not currently trigger ref deletion. Manual deletion through the available GitHub connector is unsupported.

## What Changes

- Add authenticated, fail-closed GitHub Actions cleanup on main push.
- Require exact current branch SHA match with a closed merged PR targeting main.
- Preserve main, all unmatched branches, and branches with open PRs.
- Verify actual remote absence after DELETE; add unit tests and source CI coverage.

## Impact

Repository ref lifecycle only. No WordPress runtime or dependency behavior changes.
