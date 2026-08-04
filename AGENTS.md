# Parish Bulletins Development Workflow

This directory is the active LocalWP plugin and the `dev` worktree for `twitchd8/parish-bulletins`.

## Repository Roles

- Develop and test changes in this directory on `dev`.
- The linked stable checkout is `C:\Users\aschmitt\LocalWP-Sites\ATS-WP-DEV\app\github-repositories\parish-bulletins` on `main`.
- Do not manually mirror files between the two paths. Both paths are Git worktrees of the same repository.
- Keep `main` stable. Merge tested `dev` work through a reviewed pull request or an explicit release workflow, then tag releases from `main`.
- Do not add this directory back to the `ats-wp-dev` parent repository or convert it into a submodule.

## Verification

- Run PHP syntax checks on changed PHP files.
- Verify the active LocalWP output at `http://ats-wp-dev/bulletins/` and relevant single-Bulletin URLs.
- Confirm `git status -sb` from this directory before committing.
