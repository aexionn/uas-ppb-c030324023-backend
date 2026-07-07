# Campus Admission Registration

A system where prospective students apply for admission to the campus: a web service (backend + web UI for candidates and academic admins) and a Flutter mobile app, backed by MySQL.

## Language

**Candidate**:
A prospective student applying for admission. Not yet an enrolled student; has no campus-issued identity beyond their Account.
_Avoid_: student, user, applicant

**Academic Admin**:
Campus staff who manage/review Applications through the web UI only (no mobile access).
_Avoid_: admin (alone), staff, operator

**Account**:
A login identity with a role: Candidate or Academic Admin. Candidates log in with NISN + password; Admins with username + password. Username and private email are captured at initial registration and displayed only on the Account Page. Admin accounts are seeded manually, never self-registered.
_Avoid_: profile, user

**NISN**:
The candidate's national student number, used as the login identifier.

**Application**:
The admission data a Candidate submits (from web or mobile): personal data, school origin, chosen program, parents' data, and the Candidate's photo. Each submission or edit restarts a 10-minute edit window; a Candidate gets at most 3 edits. Lock is enforced server-side; clients only display a countdown.
A Candidate has exactly one Application (1:1 with Account).
_Avoid_: registration data, form data, submission

**Locked**:
The state of an Application the Candidate can no longer edit: 10 minutes elapsed since last submit/edit, or all 3 edits used. Applies to Candidates only — Academic Admins can edit any Application at any time.
_Avoid_: closed, frozen, final

**Account Page**:
The page (on both web and mobile) showing the Account's NISN, username, and private email (view-only), plus a change-password action.

**Status**:
The admin verdict on an Application: Submitted → Accepted or Rejected. Set by an Academic Admin; visible to the Candidate. Once Status is no longer Submitted, the Application is Locked for the Candidate regardless of time or edits remaining.
_Avoid_: state (use for Locked), result
