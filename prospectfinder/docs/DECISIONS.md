# Architectural Decisions

## ADR-001 — Project lives in `prospectfinder/` subdirectory (2026-11-26)
The repository root already hosts an unrelated static site (greeting card, `build.sh`, `dist/`).
To avoid disturbing it, ProspectFinder is self-contained under `prospectfinder/`.

## ADR-002 — Laravel 13 / PHP 8.3 (2026-11-26)
Laravel 13 is the current stable major (13.x mature at the time of setup) and supports the
installed PHP 8.3.

## ADR-003 — PHPUnit over Pest (2026-11-26)
PHPUnit ships with Laravel and the playbook allows either. Avoids an extra dependency.

## ADR-004 — Backend tests run on PostgreSQL (2026-11-26)
Upcoming schema uses `jsonb`; SQLite would hide PostgreSQL-specific behaviour.

## ADR-005 — TypeScript 6.0 and ESLint instead of template defaults (2026-11-26)
The current Vite template ships TypeScript 7 and oxlint. The playbook requires ESLint, and
`typescript-eslint` 8.x supports TypeScript `<6.1`, so TypeScript is pinned to `~6.0`.
Revisit when typescript-eslint supports TypeScript 7.

## ADR-006 — API versioning via route prefix (2026-11-26)
All API routes are served under `/api/v1` using Laravel's `apiPrefix`.

## ADR-007 — CORS restricted to `FRONTEND_URL` (2026-11-26)
`config/cors.php` allows only the configured frontend origin instead of `*`.
