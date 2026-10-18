# Working on UniHELP

## Our branches

| Member | Branch | Task |
| --- | --- | --- |
| Jaya (@jayaganesh27) | `jaya/auth-profile` | Account and profile — #1 |
| Misha (@mishaliniselvasegaran06-star) | `misha/requests-volunteers` | Requests and volunteers — #2 |
| Charm (@charmseelan18-svg) | `charm/sessions` | Scheduling and sessions — #3 |

Accept the repository invitation before pushing. The owner also needs to confirm lecturer access. Access tasks are tracked in #5.

## Getting started

Clone into a separate working folder so you do not overwrite your running XAMPP copy.

```sh
git clone https://github.com/jayaganesh27/UniHELP.git
cd UniHELP
git fetch origin
git switch --track origin/jaya/auth-profile
```

Use your branch from the table in the last command. If the local branch already exists, use `git switch branch-name`.

## Making a change

1. Read your issue and check the behaviour on XAMPP.
2. Make a focused change.
3. Test it and record the steps, expected result, actual result, date and tester.
4. Check the diff and commit the files you changed.

```sh
git status
git diff
git add path/to/changed-file.php
git commit -m "Describe the change"
git push -u origin HEAD
```

Replace the example path and commit message. Use your own account. Keep passwords, personal photos and private database records out of commits.

## Reviewing and merging

Open a pull request to `main`, link the issue, and explain what changed and how you tested it. Ask another teammate to review the code and results. Respond to their feedback and rerun affected tests.

Our team should merge after the syntax check passes and the peer review is complete. This is a working agreement; it is not currently enforced by branch protection. The syntax check checks PHP syntax only. Database, email and browser features need separate testing.

After merging, update your local copy:

```sh
git switch main
git pull --ff-only origin main
git switch your-feature-branch
git merge main
```

Replace `your-feature-branch` with your branch. Check `git status` first and commit unfinished work before switching. Push your feature branch if it changed.

If a merge conflict happens, compare both versions, discuss the intended result, fix the file and test again. Explain the resolution in the PR. If no conflict happens, say so.

## Keeping records

Update your issue as work progresses and close it when its acceptance criteria are met. Keep links to your commits, tests, reviews and merged PRs for the presentation. See [our evidence checklist](docs/git-evidence.md).

The initial import uploaded the existing prototype together. Each member still needs to explain their own contributions. Review and understand AI-assisted changes and acknowledge the assistance used.
