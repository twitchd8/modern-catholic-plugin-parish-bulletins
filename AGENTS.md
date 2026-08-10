# Parish Bulletins Development Workflow

This directory is both the active LocalWP plugin and the single local Git repository for `twitchd8/modern-catholic-plugin-parish-bulletins`.

## Repository Roles

- Develop and test changes in this directory on `dev`.
- Keep only this local checkout. Do not create a separate stable copy just to hold `main`.
- Stable releases remain available through the `main` branch, Git tags, and GitHub.
- Keep `main` stable. Merge tested `dev` work through a reviewed pull request or an explicit release workflow, then tag releases from `main`.
- Do not add this directory back to the `ats-wp-dev` parent repository or convert it into a submodule.

## Verification

- Run PHP syntax checks on changed PHP files.
- Verify the active LocalWP output at `http://ats-wp-dev/bulletins/` and relevant single-Bulletin URLs.
- Confirm `git status -sb` from this directory before committing.
