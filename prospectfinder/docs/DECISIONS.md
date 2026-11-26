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

## ADR-008 — Enum values enforced in PHP, ranges in PostgreSQL (2026-11-26)
Status columns are `varchar` + PHP backed-enum casts. DB `CHECK` constraints built from enum
classes would make old migrations produce different schemas as enums evolve. Numeric ranges
and the suppression "email or business" rule are DB `CHECK`s since they won't drift.

## ADR-009 — Values not specified by the playbook (2026-11-26) — *review*
- `research_jobs.job_type`: discovery, website_check, contact_research, qualification
  (one per Phase 7 job).
- `research_jobs.status`: pending, running, completed, failed, cancelled
  (terminal: completed, failed, cancelled).
- `outreach_activities.status`: planned, contacted, replied, interested, not_interested, won, lost.

## ADR-010 — Additional constraints beyond the playbook (2026-11-26)
- `social_profiles` unique (business_id, platform, profile_url) for dedupe, mirroring contacts.
- `campaigns.filters` is `NOT NULL DEFAULT '{}'` (playbook did not mark it nullable).
- Contact emails are normalized to lowercase; uniqueness is (business_id, email). The same
  email at different businesses is allowed (e.g. a shared owner).
- `research_jobs.business_id` is `SET NULL` on business delete to preserve job history.
- `suppression_list.business_id` is `RESTRICT` on delete: deleting a business must not silently
  discard its opt-out. A future data-management feature must handle this explicitly.

## ADR-011 — Campaign ownership and status not mass assignable (2026-11-26)
`user_id` must come from the authenticated user; `status` transitions belong to orchestration.

## ADR-012 — CampaignLead is a Pivot model with its own id (2026-11-26)
Accessible both as `$campaign->businesses` (pivot exposed as `->lead`) and directly via
`$campaign->leads` / `CampaignLead`, so leads can later be addressed by id in the API.

## ADR-013 — Demo seeding is local-only and labelled (2026-11-26)
Playbook rule: mocked data only in tests and clearly labelled demos. The seeder refuses to run
outside local/testing and labels everything `[Demo]` with reserved domains.
