# UniHELP
### BIT216 Software Engineering Principles — Assignment 2

UniHELP is our student support and volunteer platform. Students can request help, browse volunteers and view scheduled support sessions.

## Our team

| Member | GitHub | Assigned module |
| --- | --- | --- |
| Jayaganesh A/L Palanisamy | @jayaganesh27 | UC1: Registration, OTP login and profile |
| Mishalini S. Selvasegaran | @mishaliniselvasegaran06-star | UC2: Requests and volunteer features |
| Charmaine Jayarina Suresh Jayaseelan | @charmseelan18-svg | UC3: Scheduling and upcoming sessions |

## Iteration 1

- Register a student account.
- Log in with a password and six-digit OTP.
- Edit profile details and upload a profile photo.
- Submit a support request with a category, description and preferred time.
- Browse volunteers using a basic skill filter.
- Accept a request and schedule a session as a volunteer.
- View upcoming sessions as either participant.

Volunteer roles and skills are preset for this demonstration. Automatic matching, editable skills, availability, rescheduling, cancellation and feedback are planned for later development.

## Technologies

PHP 8.2, MySQL/MariaDB, HTML, CSS and JavaScript. We run the prototype locally using XAMPP.

## How to run

1. Put the project in `C:\xampp\htdocs\unihelp`. The `index.php` file should be directly inside this folder.
2. Start **Apache** and **MySQL** in XAMPP.
3. For a fresh setup, import `schema.sql` using phpMyAdmin. Do not reimport it over a database you want to keep.
4. Check the database details in `config.php`. The default database is `unihelp`, username `root`, and a blank local password.
5. Set `APP_URL` to `http://localhost/unihelp`.
6. Open **http://localhost/unihelp/index.php**.

The uploads folder must be writable. Uploaded personal photos are excluded from Git.

## Demo accounts

| Account | Email | Role |
| --- | --- | --- |
| Jaya | `jaya@university.edu` | Student |
| Misha | `misha@university.edu` | Volunteer |
| Charm | `charm@university.edu` | Volunteer |

**Local demo password:** `UniHelp123!`

These are example accounts, not real email inboxes. The schema contains the corrected demo passwords; it does not update an existing database automatically. Additional Marcus and Wei Ming accounts are included as sample data.

## OTP in the local demo

The app uses PHP `mail()`. Email delivery needs a configured mail service and has not yet been verified. If sending fails, `SHOW_OTP_IF_EMAIL_FAILS` allows a **Demo code** to appear on screen.

Use the current code from the verification page. Create a new request with a future date for the presentation because the seeded requests contain example dates.

## Testing progress

- All 12 PHP files passed local syntax checks.
- Twelve isolated smoke checks passed for selected request, profile and session paths.
- Those smoke checks used a disposable SQLite copy with a MySQL query adapted for the test.
- Full browser/MySQL testing, email delivery, profile photo uploads and Selenium execution still need recorded results.

Fonts and default avatars use external services, so their appearance may depend on internet access.

## Team workflow

| Task | Branch | Issue |
| --- | --- | --- |
| Account and profile checks | `jaya/auth-profile` | [#1](https://github.com/jayaganesh27/UniHELP/issues/1) |
| Requests and volunteer checks | `misha/requests-volunteers` | [#2](https://github.com/jayaganesh27/UniHELP/issues/2) |
| Scheduling and session checks | `charm/sessions` | [#3](https://github.com/jayaganesh27/UniHELP/issues/3) |

We track tasks using issues and work on module branches. Each member should push their own changes and ask another member to review the pull request before merging.

[PR #6](https://github.com/jayaganesh27/UniHELP/pull/6) adds our contribution guide, templates and PHP syntax check. Its automated check passed; review and merging were pending when this README was updated. The initial commit uploaded the existing prototype together.

## AI assistance

ChatGPT/Codex assisted with documentation, repository setup, demo-account fixes and isolated checks. The team needs to review and understand the assisted work and complete its own contribution and testing records.
