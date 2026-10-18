# UniHELP

Student Support and Volunteer Platform. BIT216 group project, Iteration 1.

## Team responsibilities

| Member | Use case | Iteration 1 scope |
|---|---|---|
| Jayaganesh A/L Palanisamy (Jaya) | UC1: Student and volunteer profiles | Registration, password/OTP login, personal profile and photo |
| Mishalini S. Selvasegaran (Misha) | UC2: Requests and volunteers | Support requests, basic volunteer filter, incoming queue and basic acceptance |
| Charmaine Jayarina Suresh Jayaseelan (Charm) | UC3: Support sessions | Scheduling and upcoming sessions for both participants |

Basic acceptance connects the requests and sessions modules. Volunteer roles and filter skills are preset for the Iteration 1 demonstration.

## Implemented scope

- Student registration with required-field, password and duplicate-account checks.
- Password login followed by six-digit OTP verification.
- View/edit name and contact number, with a profile photo upload handler.
- Submit support requests with category, description and preferred date/time.
- Browse volunteers with a basic skill filter.
- Volunteer queue with manual acceptance and session scheduling.
- Upcoming sessions for the student and volunteer.

This copy excludes volunteer conversion, editable skills, weekly availability, automatic matching, rejection, request history, rescheduling, booked-session cancellation, completion and feedback.

## Stack

PHP 8.2, PDO, MySQL/MariaDB, HTML, CSS and vanilla JavaScript. XAMPP provides the local Apache/PHP/database environment.

## Run locally

1. Download/clone this repository into `C:\xampp\htdocs\unihelp`. Keep `index.php` directly inside that directory.
2. Start Apache and MySQL in XAMPP.
3. For a fresh database, import `schema.sql` in phpMyAdmin. It creates `unihelp` and the tables/demo records. Do not reimport over an existing database as an upgrade.
4. Check `config.php`. Defaults use database `unihelp`, user `root`, an empty local database password, and `APP_URL` set to `http://localhost/unihelp`.
5. Open `http://localhost/unihelp/index.php` in your browser.

Use your actual local database settings. Uploaded profile images are excluded from Git. The application creates the uploads directory when needed, and it must be writable.

## Local demo accounts

| Display name | Email used to log in | Role |
|---|---|---|
| Jaya | `jaya@university.edu` | Student |
| Misha | `misha@university.edu` | Volunteer |
| Charm | `charm@university.edu` | Volunteer |

Local demonstration password: `UniHelp123!`. These are example accounts and example email addresses, not verified inboxes. This repository schema includes the repaired demo hashes and team names. Importing it does not update an already-existing database.

The additional Marcus and Wei Ming demo fixtures remain for browsing/request examples. Their seed password is also the local demonstration password.

## OTP and images

The mail helper uses PHP `mail()`. XAMPP needs a configured mail transport for delivery. `SHOW_OTP_IF_EMAIL_FAILS` enables the on-screen **Demo code** when sending fails. The demo emails above do not establish real email delivery. Explain the fallback during a local demonstration.

Default avatar fallback and fonts use external services, so appearance can depend on internet access.

## Verification and known limits

All 12 PHP source files passed syntax checks. Twelve targeted runtime checks passed in an isolated PHP CLI/SQLite copy, covering profile updates, scoped actions, request creation/browsing, manual acceptance and both upcoming-session views. A MySQL-specific aggregate was adapted only in that disposable test copy.

Those checks do not establish complete browser/MySQL acceptance, actual email delivery, profile upload behaviour or Selenium execution. Record actual outcomes and screenshots in the group testing workbook before claiming completion. Scheduling dates should be in the future. The earlier seed request dates are example historical values, so create a new request for the presentation.

## Presentation demonstration

Register/login as a student, verify the OTP, edit the profile, submit a future-dated request, browse/filter volunteers, then log in as a preset volunteer in a separate browser session. Accept and schedule the request. Show the same upcoming session in both accounts.

## Git collaboration

This initial import combines the group's existing Iteration 1 source. It does not establish individual authorship or earlier collaboration history. Each member should make real changes under their own account, use a feature branch, open a pull request, and have another member review the actual change. Do not create artificial commits or backdate work for assessment evidence.

Suggested branch names are `jaya/auth-profile`, `misha/requests-volunteers` and `charm/sessions`. These are workflow suggestions, not claims that branches or reviews already exist.

AI assistance: ChatGPT helped prepare the repository packaging, documentation, demo-account repairs and isolated checks. Group review and individual contribution evidence remain the responsibility of the members.
