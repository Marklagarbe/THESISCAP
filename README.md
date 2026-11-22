# ThesisCap

Thesis and Capstone Milestone Monitoring System (Laravel 11, Blade, MySQL).
Design: dark blue glass UI that matches the login page. No npm or Vite needed: CSS and JS are in `public/`.

## Requirements
- PHP 8.2+ with extensions: mbstring, openssl, pdo_mysql, fileinfo, ctype, json, tokenizer, xml
- Composer
- MySQL / MariaDB (XAMPP is fine)

## Setup (4 steps)
1. Create an empty database named `thesiscap` in phpMyAdmin (utf8mb4_unicode_ci).
2. In this folder run:
   ```bash
   composer install
   php artisan migrate --seed
   php artisan serve
   ```
3. Open http://127.0.0.1:8000
4. Sign in with a demo account (password for all: `password`).

`.env` is already included with an APP_KEY. If you change the DB name or password, edit `.env`.
If you ever need a new key: `php artisan key:generate`.

To use SQLite instead of MySQL, set `DB_CONNECTION=sqlite` and `DB_DATABASE=` (absolute path to `database/database.sqlite`) in `.env`.

## Demo accounts
| Role | Email |
|---|---|
| Administrator | admin@thesiscap.test |
| Project Coordinator | coordinator@thesiscap.test |
| Instructor (BSIT) | instructor@thesiscap.test |
| Adviser (BSIT) | adviser@thesiscap.test |
| Student Project Manager / Group Leader (BSIT) | leader@thesiscap.test |
| Student Member (BSIT) | member@thesiscap.test, member2@thesiscap.test |
| BSHM group | leader.hm@thesiscap.test, member.hm@thesiscap.test, instructor.hm@thesiscap.test, adviser.hm@thesiscap.test |
| BSBA group (no staff assigned yet) | leader.ba@thesiscap.test |

## Roles and what they can do
- **Student Member** (`student`): own landing page (`/member`), milestones, assigned tasks, submit files, feedback and revisions, personal progress.
- **Student Project Manager / Group Leader** (`leader`): own landing page (`/manager`), register project, add or remove members, assign tasks, member progress, group deadlines, group submissions.
- **Instructor**: assigned groups, student progress, review submissions, give feedback, request revisions.
- **Adviser**: advised projects, review chapters, recommendations, monitor revisions, approve milestones.
- **Project Coordinator**: all projects, overall progress, phases and milestones (add, edit dates, delete), instructor and adviser assignments, delayed projects, accomplishment report (print and CSV).
- **Administrator**: users (create, edit, disable, delete), roles and permissions matrix, settings and departments, records, system-wide reports, activity logs.

Chapters and Manuscripts (sidebar) first show a department picker (BSIT, BSHM, BSBA, ...) and then the items of that department.
Add departments in Admin > Settings.

## Feature checklist (all 15 from the brief)
Registration and login, profile management, project creation, group and member management, phases and milestones,
task assignment and deadlines, **progress updates** (project > Progress updates tab), file and document submission,
**revision and version history** (Version history card on every submission), instructor feedback and approval,
**project timeline** (project > Timeline tab), **individual task activity history** (open any task),
group progress dashboard (Manager home), **notifications and reminders**, progress and completion reports.

Reminders for due-soon and overdue tasks and milestones are created automatically when a user signs in.
To send them to everyone at once (or from Windows Task Scheduler): `php artisan thesiscap:remind`

## Sample process included
The seeder fills the system with a process that has already been used, so every screen has real content:
- **SagipAlert (BSIT, capstone)**: registered 62 days ago, instructor and adviser assigned, Topic approval needed one revision, Chapter 1 needed one revision and was approved, Chapter 2 version 2 is waiting for the adviser and is overdue (shows as delayed), tasks with full activity history, progress updates, feedback trail, notifications and activity logs.
- **Guest Experience Mapping (BSHM, thesis)**: earlier in the process.
- **Digital Marketing Adoption (BSBA, thesis)**: just registered with no instructor or adviser yet, so the Coordinator can assign them.
To load it on an existing database: `php artisan migrate:fresh --seed` (this erases all data).

## Registration needs a registered ID
Nobody can create an account unless their ID number is already in the database (Admin > **Registered IDs**).
- Formats: students `ST-1234567`, teachers/instructors `TE-3346776`, advisers `AD-5500123`, coordinators `CO-7700001` (prefix, dash, 4 to 10 digits).
- An ID works once. After registering, it is linked to that account and cannot be reused.
- The ID decides which roles are allowed: a student ID can register as Student Member or Student Project Manager; a TE- ID only as Instructor; AD- only as Adviser; CO- only as Project Coordinator.
- If the admin attached a department to the ID, it is applied automatically.
- Admins add IDs one by one or paste many at once (one per line: `ID` or `ID, Full name`).
- Sample unused IDs for testing: ST-1000101, ST-1000102, ST-1000103, TE-3346800, TE-3346801, AD-5500200, CO-7700100 (shown on the register page while SHOW_DEMO_ACCOUNTS=true).
- Existing database: `php artisan migrate` then `php artisan db:seed --class=RegisteredIdSeeder` (keeps your data).

## Notes
- Uploaded files are stored in `storage/app/submissions` and are downloaded only through the app (login required).
- Roles & Permissions page changes take effect immediately.
- Set `SHOW_DEMO_ACCOUNTS=false` in `.env` to hide the demo dropdown on the login page.
- Fonts load from Google Fonts. Offline, the UI falls back to system fonts.
