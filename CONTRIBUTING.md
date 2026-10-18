# Contributing to UniHELP

## Team and scope

| Member | GitHub | Iteration 1 responsibility | Branch | Issue |
| --- | --- | --- | --- | --- |
| Jaya | @jayaganesh27 | UC1 registration, OTP login, profile | `jaya/auth-profile` | #1 |
| Mishalini | @mishaliniselvasegaran06-star | UC2 requests, volunteer filtering, acceptance | `misha/requests-volunteers` | #2 |
| Charmaine | @charmseelan18-svg | UC3 scheduling and upcoming sessions | `charm/sessions` | #3 |

The owner must invite both teammates through Settings > Collaborators. They must accept before pushing. Confirm lecturer access separately. The workflow setup is tracked in #4 and access/evidence in #5.

## Work from your own account

Clone once into your own working folder. Do not overwrite a running XAMPP copy or its database.

```sh
git clone https://github.com/jayaganesh27/UniHELP.git
cd UniHELP
git fetch origin
git switch --track origin/jaya/auth-profile
```

Misha and Charm replace the last branch with their branch from the table. If a local branch already exists, use `git switch branch-name` instead. Check `git status` before changing branches. Commit your current work before synchronising; do not discard uncommitted changes.

Read the linked issue, reproduce the behaviour on XAMPP, and make a focused fix or a useful test/documentation contribution based on actual results. Record the environment, tester, date, steps, expected result and observed result. Commit only files you reviewed:

```sh
git diff
git add path/to/changed-file.php
git commit -m "Fix specific observed behaviour"
git push -u origin HEAD
```

Use your own Git identity and explain the actual change in the commit message. Do not share credentials, commit uploaded personal photos, or include real database exports. The seeded accounts are local demonstration fixtures.

## Pull requests and reviews

Open a pull request targeting `main`. Link its issue, explain the change, and list only tests actually executed. PHP syntax CI does not test database behaviour, email delivery or browser flows. Ask a different teammate to review the diff and evidence. That person should leave substantive feedback or approve after checking it; authors cannot provide their own peer approval. Resolve feedback and rerun affected checks before merging. Merge after the check passes and the peer review is complete. This is the team policy; verify repository settings separately before describing it as enforced protection.

After merging, synchronise each local clone:

```sh
git switch main
git pull --ff-only origin main
git switch your-feature-branch
git merge main
```

Push the feature branch if it changed. If a conflict occurs, inspect both versions, agree the intended behaviour, edit the affected files, test, then commit the resolution. Document the actual files and decision in the PR. If no conflict occurs, record that accurately; do not manufacture one for assessment.

## Assessment evidence

Use real links to issues, commits, reviews, checks and merge commits. Close an issue only when its acceptance criteria are met. The initial source import does not establish individual authorship or historical continuous development. See [the evidence checklist](docs/git-evidence.md). AI assistance must be reviewed and understood by the member submitting the work.
