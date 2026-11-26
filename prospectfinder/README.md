# ProspectFinder

A private lead generation and prospect research platform. It discovers local businesses,
determines whether they have a dedicated website, researches publicly available business
contact details with source traceability, and scores the results as prospects.

> **Status:** Phases 0–2 complete (setup, database, authentication and API foundation).
> No discovery, research or lead features exist yet. See [docs/IMPLEMENTATION_STATUS.md](docs/IMPLEMENTATION_STATUS.md).

## Structure

```
prospectfinder/
├── backend/    Laravel 13 REST API (PHP 8.3, PostgreSQL, Redis, Sanctum)
├── frontend/   React 19 + TypeScript + Vite 8 + Tailwind CSS 4
└── docs/       Architecture, database, API, provider and process documentation
```

## Requirements

| Tool       | Version tested |
|------------|----------------|
| PHP        | 8.3 (extensions: pdo_pgsql, redis or predis, mbstring, xml, curl, intl) |
| Composer   | 2.8            |
| Node.js    | 22             |
| npm        | 10             |
| PostgreSQL | 16             |
| Redis      | 7              |

## Setup

### 1. Databases

```bash
createdb prospectfinder
createdb prospectfinder_test   # used by the backend test suite
```

### 2. Backend

```bash
cd backend
composer install
cp .env.example .env           # then set DB_PASSWORD etc.
php artisan key:generate
php artisan migrate
php artisan prospectfinder:create-user you@example.com --name="Your Name"   # there is no signup page
php artisan serve              # http://localhost:8000
```

For local demo data instead: `php artisan db:seed` (local only; login `demo@prospectfinder.test` / `password`).

Queue worker (required from Phase 7 onward):

```bash
php artisan queue:work redis
```

### 3. Frontend

```bash
cd frontend
npm install
cp .env.example .env
npm run dev                    # http://localhost:5173
```

Open http://localhost:5173 — the status page shows whether the API, database and Redis are reachable.

## Checks

| Command | Where | Purpose |
|---------|-------|---------|
| `php artisan test` | backend | PHPUnit tests (uses `prospectfinder_test`) |
| `vendor/bin/pint` | backend | Code formatting |
| `npm test` | frontend | Vitest + React Testing Library |
| `npm run lint` | frontend | ESLint (typescript-eslint strict) |
| `npm run typecheck` | frontend | TypeScript strict mode |
| `npm run build` | frontend | Production build |

See [docs/DEVELOPMENT.md](docs/DEVELOPMENT.md) for details.
