# PRD: Campus Admission Registration

Status: ready-for-agent

## Problem Statement

Prospective students (Candidates) need a way to apply for campus admission by submitting their personal, family, and school data, and to make a limited number of corrections shortly after submitting. Academic Admins need a way to review those Applications and record a verdict. Today there is no system: no self-service registration, no place to submit an Application, no way for an Admin to accept or reject it, and no shared source of truth. Candidates want to apply and check their result from either a phone or a browser; Admins want to manage everything from a browser.

## Solution

A Laravel web service backed by MySQL that serves two clients:

- A **web UI** (Blade + session auth) used by Candidates (register, submit/edit Application, view status, manage account) and by Academic Admins (review Applications, set verdicts, manage Programs, manage own account).
- A **JSON API** (`/api/v1`, JWT auth) consumed by a Flutter mobile app that lets Candidates register, submit their Application, edit it while still editable, and view their status and account.

A Candidate self-registers an Account (NISN, username, private email, password) and submits exactly one Application. After submitting, the Candidate may edit it up to 3 times; each submit or edit restarts a 10-minute editing window. Once the window closes, the edit budget is exhausted, or an Admin has set a verdict, the Application becomes Locked for the Candidate. Academic Admins can edit or delete any Application at any time and set its Status to Accepted or Rejected, which the Candidate can then see.

## User Stories

### Candidate — account
1. As a Candidate, I want to register an Account with my NISN, username, private email, and password, so that I can access the system.
2. As a Candidate, I want to register from either the mobile app or the website, so that I am not forced onto one platform first.
3. As a Candidate, I want to be prevented from registering with an NISN, username, or email that is already taken, so that identities stay unique.
4. As a Candidate, I want my NISN validated as 10 digits, so that I cannot submit an obviously malformed identifier.
5. As a Candidate, I want to log in with my NISN and password, so that I can reach my Application.
6. As a Candidate, I want an Account Page showing my NISN, username, and private email, so that I can see the identity details I registered with.
7. As a Candidate, I want to change my password from the Account Page, so that I can keep my Account secure.

### Candidate — application
8. As a Candidate, I want to submit one Application containing my personal data, so that the campus has my admission information.
9. As a Candidate, I want to enter my full name, birth place, birth date, gender, address, and phone, so that my personal identity is recorded.
10. As a Candidate, I want to record my school of origin, so that my academic background is known.
11. As a Candidate, I want to choose my desired Program from a fixed list, so that I apply to a valid program without typos.
12. As a Candidate, I want to enter my father's and mother's names and jobs, so that my family data is recorded.
13. As a Candidate, I want to select my parents' combined income from a fixed set of brackets, so that my economic background is recorded consistently.
14. As a Candidate, I want to upload my photo as part of the Application, so that my file is complete.
15. As a Candidate, I want the photo to be required at submission, so that I cannot submit an incomplete Application.
16. As a Candidate, I want to submit my Application from either the mobile app or the website, so that I can use whichever is convenient.
17. As a Candidate, I want to edit my submitted Application, so that I can correct mistakes.
18. As a Candidate, I want each edit to give me a fresh 10-minute window, so that I have time to finish a correction I just started.
19. As a Candidate, I want to be allowed at most 3 edits, so that the rules are clear and bounded.
20. As a Candidate, I want to be blocked from editing once 10 minutes have passed since my last change, so that I understand the deadline is real.
21. As a Candidate, I want to be blocked from editing once I have used all 3 edits, so that I know my budget is spent.
22. As a Candidate, I want to be blocked from editing once an Admin has decided my Application, so that a verdict is not undermined by later changes.
23. As a Candidate, I want to see how much editing time I have left, so that I can decide whether to make a change now.
24. As a Candidate, I want to see how many edits I have remaining, so that I can spend them wisely.
25. As a Candidate on mobile, I want the save button to disable and a banner to appear the moment my window expires while editing, so that I am not surprised by a rejected save.
26. As a Candidate, I want to view my Application data at any time, so that I can confirm what I submitted.
27. As a Candidate, I want to see my Status (Submitted, Accepted, or Rejected), so that I know the outcome.
28. As a Candidate whose Application was deleted by an Admin, I want to be able to submit a fresh Application, so that I get another chance if appropriate.

### Academic Admin
29. As an Academic Admin, I want to log in with my username and password on the website, so that I can access admin tools.
30. As an Academic Admin, I want my account to be provisioned for me rather than self-registered, so that admin access stays controlled.
31. As an Academic Admin, I want to see a list of all Applications, so that I can review submissions.
32. As an Academic Admin, I want to search Applications by name or NISN, so that I can find a specific Candidate.
33. As an Academic Admin, I want to filter Applications by Status and by Program, so that I can focus my review.
34. As an Academic Admin, I want to open an Application and see its full data and photo, so that I can evaluate it.
35. As an Academic Admin, I want to set an Application's Status to Accepted or Rejected, so that I record the admission verdict.
36. As an Academic Admin, I want to edit any Application at any time regardless of the Candidate's lock, so that I can fix errors.
37. As an Academic Admin, I want to delete an Application (removing its data and photo file but keeping the Candidate's Account), so that bad entries can be cleared and the Candidate can re-apply.
38. As an Academic Admin, I want to manage the list of Programs (create, edit, remove), so that Candidates choose from an accurate set.
39. As an Academic Admin, I want my own Account Page with a change-password action, so that I can maintain my credentials.

### Cross-cutting
40. As a Candidate using the mobile app, I want the app to attach my token automatically to requests, so that I stay logged in across screens.
41. As a Candidate, I want the mobile app to reflect the server's lock state rather than guess locally, so that what I see matches what the server enforces.
42. As any user, I want UI text and error messages in Indonesian, so that the system is usable in its intended context.
43. As a Candidate, I want to be prevented from viewing or editing another Candidate's Application, so that my data stays private.

## Implementation Decisions

### Architecture
- **Backend**: Single Laravel application serving both the Blade web UI (session guard) and the `/api/v1` JSON API (JWT). One repo, one deploy.
- **Mobile**: Flutter app living in the same repo under `mobile/` (monorepo; backend at root). Uses Provider (state), dio (HTTP, with an interceptor that attaches the JWT), and shared_preferences (token storage). The Flutter app is a client only, talking to the backend over HTTP; all enforceable rules live in the backend.
- **Admin has no API** — admin capabilities are web-only via Blade + session. The API surface is Candidate-only.

### Authentication
- One `accounts` table with a `role` column (`candidate` | `admin`). Candidates log in with NISN + password; Admins with username + password. Admin accounts are seeded manually (seeder/SQL), never self-registered.
- Passwords hashed with Laravel's default bcrypt (`Hash`).
- **JWT for the API** using `firebase/php-jwt`, HS256, secret in `JWT_SECRET` (`.env`), 24h expiry, no refresh token. Claims: `sub` = account id, `role`. A custom middleware verifies the token on `/api/v1/*` protected routes. See ADR-0001 — this is a deliberate choice over Laravel Sanctum.

### Lock model (server-enforced)
An Application is **Locked for the Candidate** when any of:
- `now > last_submitted_at + 10 minutes`, or
- `edits_used >= 3`, or
- `status != submitted`.

`last_submitted_at` is set on create and reset on every accepted edit; `edits_used` increments on every accepted edit (not on the initial create). Academic Admins bypass the lock entirely. Clients only display a countdown derived from server values; they never make the authoritative decision.

### Data model (frozen)
- **accounts**: id, role (enum candidate|admin), nisn (nullable, unique, 10 digits — null for admins), username (unique), email (unique), password (bcrypt), timestamps.
- **programs**: id, name.
- **applications**: id, account_id (FK, unique → 1:1 with Account), program_id (FK), full_name, birth_place, birth_date (date), gender (enum L/P), address (text), phone, school_origin, father_name, father_job, mother_name, mother_job, parents_income (enum: `<1jt` | `1-3jt` | `3-5jt` | `>5jt`), photo_path, status (enum submitted|accepted|rejected, default submitted), edits_used (tinyint, default 0), last_submitted_at (datetime), timestamps.
- A Candidate has exactly one Application (unique `account_id`).

### Photo storage
- Stored as a file on disk under `storage/app/public/photos/` with a random/hashed filename; only the path is kept in the DB. `php artisan storage:link` exposes it. Application responses include a public absolute `photo_url` (unguessable filename, no per-request auth gate). Max 2MB, jpg/png, required at submit.

### API contract (`/api/v1`)
- `POST /register` — nisn, username, email, password.
- `POST /login` — returns JWT.
- `GET /me` — Account Page data (nisn, username, email).
- `PUT /me/password` — change password.
- `GET /programs` — list for the Program dropdown.
- `POST /application` — multipart, photo required; creates the single Application.
- `GET /application` — own Application data plus `status`, `edits_used`/`edits_remaining`, `locked` (bool), and `editable_until` (timestamp) so the client can drive a countdown without local clock math.
- `PUT /application` — multipart, photo optional; server enforces the lock and rejects when Locked.
- **Error shape**: `{ message, errors: { field: [...] }, code }` following Laravel's validation shape, with a domain `code` for lock errors: `APPLICATION_LOCKED`, `EDITS_EXHAUSTED`. Clients switch on `code`, not on message text. UI/messages in Indonesian.

### Web UI (Blade)
- Candidate pages: register, login, Application form (create; edit while unlocked with countdown), Application view (data + status + edits remaining), Account Page.
- Admin pages: Application list (search by name/NISN, filter by status + program), Application detail (full data + photo; Accept / Reject / Edit / Delete), Programs CRUD, own Account Page.

## Testing Decisions

- **What a good test is here**: exercises externally observable behavior through the real HTTP boundary — request in, JSON/status out, DB state as a side effect — with no assertions on private methods or internal class shapes. Rules are verified by their observable outcome (a rejected edit, a returned `code`, a persisted file), not by how the code computes them.
- **Primary seam — the `/api/v1` HTTP boundary** (Laravel feature tests through real routes and the JWT middleware against a test database). This single seam covers register/login and JWT issuance with correct `role`; the full lock state machine (submit → edit resets window; 3-edit cap → `EDITS_EXHAUSTED`; past-deadline → `APPLICATION_LOCKED`; verdict ≠ submitted → locked); the `GET /application` shape (`locked`, `editable_until`, `edits_remaining`); uniqueness (nisn/username/email) and NISN `digits:10`; photo-required-at-submit and that a multipart upload actually lands a file; and authorization (a Candidate cannot read or edit another Candidate's Application).
- **Time control point**: the lock depends on "now", so tests use `Carbon::setTestNow()` to fast-forward past the 10-minute window instead of sleeping. This is a control point on the primary seam, not a new seam.
- **Deliberately not seamed**: the admin Blade web UI gets at most a couple of thin smoke tests (its domain logic is the same logic already covered at the API seam); the Flutter app is out of scope for this backend PRD.
- **Prior art**: standard Laravel feature tests using `RefreshDatabase`, `actingAs`/bearer-token helpers, `Storage::fake('public')` for upload assertions, and `Carbon::setTestNow` for time — the conventional Laravel testing toolkit. As this is a greenfield repo, these tests establish the pattern rather than follow existing ones.

## Out of Scope

- Course/semester enrollment (this is admission registration only).
- Multiple Applications per Candidate or applying to multiple Programs (1:1 Account↔Application).
- Refresh tokens, token revocation/blacklist, password reset via email, email verification.
- Admin self-registration or an admin-management UI for creating other admins (admins are seeded).
- Candidate-editable username/email (Account Page is view-only apart from change-password).
- A separate JS SPA for the web frontend (Blade only).
- An admin JSON API and admin access from mobile.
- Real-time notifications of verdicts (Candidate polls/refreshes to see Status).
- Production-grade secure token storage on mobile (`shared_preferences` is accepted for this scope).

## Further Notes

- Glossary: see `CONTEXT.md` (Candidate, Academic Admin, Account, NISN, Application, Locked, Status, Account Page). PRD language follows it and avoids the listed synonyms.
- Decision record: `docs/adr/0001-jwt-instead-of-sanctum.md` documents the JWT-over-Sanctum choice.
- The repo is greenfield (no `git init` yet, no GitHub remote). This PRD is published as local markdown under `.scratch/`; it can be moved to a GitHub issue if a remote is created.
- `programs` should be seeded with an initial set so the Candidate dropdown is non-empty on first run.
