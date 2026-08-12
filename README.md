# Custodia — Prison Management System

A custody and welfare management system for a correctional facility: admissions, housing,
sentences, court cases, medical care, visits, movements and releases — with a full audit trail
on every action.

## Why I built this

Nigeria's correctional system has a records problem before it has anything else.

Most of what goes wrong in a Nigerian prison is downstream of not knowing things: who is in this
facility, what legal authority is holding them, when they are due out, which court date they
missed, who authorised the transfer. A large share of the inmate population is awaiting trial
rather than serving a sentence, facilities run well over their designed capacity, and record
keeping in many of them is still paper — ledgers, case files in envelopes, entries in
handwriting. When the paper is the only copy, a lost file is a lost person. People sit past
their release date because nobody can produce the document that proves the date. Nobody set out
for that to happen; the system just has no way to notice.

That's a software problem, and a fairly ordinary one. So I built the thing I'd want if I were
handed that brief.

The design follows from the failure modes rather than from a feature list:

| The problem | What the system does about it |
|---|---|
| No paper trail of who changed what | Every mutation writes an append-only audit entry — actor, action, before/after — inside the same database transaction as the change, so a state change and its record cannot come apart |
| People held with no traceable legal basis | Admission can't progress past intake until the legal authority reference is recorded; sentences carry court, offence, legal status and parole eligibility |
| People held past their release date | Sentence end dates and parole eligibility are first-class fields, not notes in a margin |
| Releases happening without checks | Release runs a five-step chain — legal, sentence, property, documentation — that ends in a supervisor sign-off no officer can bypass |
| Overcrowding only visible once it's a crisis | Occupancy is derived live from active assignments at cell, wing and block level |
| "Who authorised this?" | Four roles enforced by Laravel Policies; the acting user is recorded on every action |
| Medical information treated as general staff information | Medical records sit behind a dedicated Medical role — officers and supervisors see operational alerts only, never clinical detail |

It's a portfolio project, not a deployment. But the constraints I designed against are real ones.

## Stack

- Laravel 13 (API), Laravel Sanctum (SPA cookie auth)
- Vue 3 + TypeScript + Vue Router + Pinia, served via Vite through a single Blade shell
- PostgreSQL
- Tailwind CSS 4 (+ `@tailwindcss/forms`), Lucide icons
- Docker (optional — see below)

## What's in it

**Custody**

- **Admissions** — a staged intake workflow: create the prisoner record, record legal authority,
  initial assessment, security classification, medical screening (medical staff only), housing
  assignment, complete. Each stage gates the next.
- **Prisoners** — register, search, view, archive.
- **Prison structure** — Facility → Block → Wing → Cell, with occupancy derived from active
  assignments rather than stored (so it can't drift).
- **Housing assignments** — a history table with `started_at`/`ended_at` rather than a
  `prisoners.cell_id` column, so every prisoner keeps a full cell timeline.
- **Movements** — transfers and escorts with a requested → approved → departed → arrived →
  returned lifecycle.
- **Property** — belongings logged in at intake and signed out on discharge.

**Case management**

- **Sentences** — case number, court, offence, start/end, sentence type, parole eligibility,
  legal status.
- **Court** — cases, hearings and legal representatives, with an upcoming-hearings view.
- **Incidents** — Reported → Under Review → Resolved, with severity and officer attribution.

**Welfare**

- **Medical** — records, appointments, prescriptions and alerts, restricted to the Medical role.
  Other staff see only the operational alerts they need to do their job safely.
- **Programmes** — rehabilitation programmes with enrolment and attendance tracking.
- **Visits** — a shared visitor registry, visit requests requiring supervisor approval (rejected
  outright for banned visitors), and desk check-in/check-out.
- **Releases** — the five-step review chain ending in supervisor approval.

**Oversight**

- **Dashboard** — population, occupancy, open incidents and available beds.
- **Audit log** — append-only trail of who did what, with before/after values.

## Roles

| Role | Can do |
|---|---|
| Admin | Everything, including staff management |
| Officer | Day-to-day custody operations — admissions, housing, incidents, property, visits |
| Supervisor | Review and sign-off — incident resolution, visit approval, movement and release approval |
| Medical | Clinical records, appointments, prescriptions, and the admission medical screening |

## Demo accounts

Seeded by `php artisan db:seed`, all with password `password`:

| Role | Email |
|---|---|
| Admin | admin@demo.com |
| Officer | officer@demo.com |
| Supervisor | supervisor@demo.com |
| Medical | medical@demo.com |

## Local setup (no Docker)

Requires PHP 8.3+, Composer, Node 22+, and a local PostgreSQL instance.

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate
```

Edit `.env` to point at your Postgres instance (`DB_HOST`, `DB_PORT`, `DB_DATABASE`,
`DB_USERNAME`, `DB_PASSWORD`), then create the database and role, e.g.:

```bash
psql -U postgres -c "CREATE ROLE custodia WITH LOGIN PASSWORD 'custodia_local_dev' CREATEDB;"
psql -U postgres -c "CREATE DATABASE custodia OWNER custodia;"
```

Run migrations and seed demo data:

```bash
php artisan migrate --seed
```

Start the app (Laravel + Vite together):

```bash
composer run dev
```

Visit `http://localhost:8010`. `SANCTUM_STATEFUL_DOMAINS` in `.env` must include whatever
host:port you're serving on (`localhost:8010,127.0.0.1:8010` by default) — Sanctum's SPA cookie
auth silently no-ops otherwise.

## Docker

A `docker-compose.yml` and `Dockerfile` are included (Postgres + a PHP-FPM-free `php artisan
serve` container, with the frontend built into the image at build time). To run:

```bash
APP_KEY=$(php artisan key:generate --show) docker compose up --build
```

Then in a separate terminal, migrate and seed:

```bash
docker compose exec app php artisan db:seed
```

The app runs on `http://localhost:8000`.

## Tests

```bash
./vendor/bin/pest
```

184 feature tests covering authentication and RBAC, prisoner CRUD, housing assignment history,
the admission and release workflows, incidents, medical access restrictions, visits, movements,
sentences, cross-cutting permission checks, and a security regression suite (login throttling,
cell capacity enforcement, state-machine guards, audit integrity). Tests run against a dedicated
`custodia_test` Postgres database (configured in `phpunit.xml`) rather than SQLite, since the
prisoner search endpoint uses Postgres's `ILIKE`.

## Architecture notes

- **Controller → Service → Model.** Business logic — sequential number generation, status
  transitions, the close-old/open-new housing assignment swap — lives in `app/Services/`.
  Controllers stay thin: authorize, delegate to a service, return a Resource. No controller
  touches `AuditService` directly.
- **Audit logging inside the transaction.** Each service method opens its own
  `DB::transaction()` and writes its audit entry within it. The state change and the record of
  who made it commit together or not at all, so the log can't drift from reality.
- **Housing history over a foreign key.** `housing_assignments` carries `started_at`/`ended_at`,
  so a prisoner's full cell history is a query rather than a reconstruction.
- **Derived occupancy.** Cell, wing and block occupancy is counted from active assignments
  instead of being stored on a row that would need to be kept in sync.
- **Backed enums everywhere.** Statuses and types are PHP backed enums cast on the model, not
  loose strings, so invalid states fail at the boundary.
- **Resources, never raw models.** Every endpoint returns an API Resource, so the JSON shape is
  explicit and internal columns don't leak.
