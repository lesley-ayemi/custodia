# Custodia — Mini Prison Management System

A deliberately small prison management system built to demonstrate authentication, RBAC via
Laravel Policies, a service-layer architecture, and an audit trail — six core modules, not forty.

## Stack

- Laravel 13 (API), Laravel Sanctum (SPA cookie auth)
- Vue 3 + TypeScript + Vue Router + Pinia, served via Vite through a single Blade shell
- PostgreSQL
- Tailwind CSS 4
- Docker (optional — see below)

## The six modules

1. **Authentication + roles** — three roles (Admin, Officer, Supervisor), enforced by Laravel
   Policies and route middleware.
2. **Prisoner management** — register, search, view, archive.
3. **Cell / housing management** — blocks and cells with derived occupancy, and a
   `housing_assignments` history table (not a `prisoners.cell_id` column) so every prisoner keeps
   a full housing timeline.
4. **Incident reporting** — Reported → Under Review → Resolved workflow.
5. **Dashboard** — prisoner/occupancy/incident stats aggregated from the modules above.
6. **Audit log** — append-only trail of who did what, with before/after values.

## Demo accounts

Seeded by `php artisan db:seed`, all with password `password`:

| Role | Email |
|---|---|
| Admin | admin@demo.com |
| Officer | officer@demo.com |
| Supervisor | supervisor@demo.com |

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

Start the app (two processes — Laravel and Vite):

```bash
php artisan serve --port=8010
npm run dev
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

25 feature tests covering authentication, prisoner CRUD and RBAC, housing assignment history,
the incident workflow, and cross-cutting permission checks. Tests run against a dedicated
`custodia_test` Postgres database (configured in `phpunit.xml`) rather than SQLite, since the
prisoner search endpoint uses Postgres's `ILIKE`.

## Architecture notes

- **Service layer**: business logic (sequential number generation, status transitions, the
  close-old/open-new housing assignment transaction) lives in `app/Services/`, not in
  controllers or models. Controllers stay thin: authorize, delegate to a service, return a
  Resource.
- **Housing history over a foreign key**: `housing_assignments` has `started_at`/`ended_at`
  rather than a single `prisoners.cell_id` column, so a prisoner's full cell history is just a
  query away.
- **Audit trail**: `AuditService` is called from controllers (which already hold the
  authenticated actor and both old/new values) rather than from model events, so log entries
  carry real intent rather than a blind before/after diff.
