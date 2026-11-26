# Database

PostgreSQL 16. Development database `prospectfinder`; test database `prospectfinder_test`.

## Conventions

- Status/type columns are `varchar` holding the value of a PHP backed enum (`app/Enums`).
  Models cast them to the enum, so invalid values throw `ValueError` before reaching the DB.
  Adding a value means changing the enum (and documenting it here). No migration is needed.
- Numeric ranges and structural invariants are enforced with PostgreSQL `CHECK` constraints.
- Emails are stored normalized (trimmed, lowercased) via `App\Support\EmailAddress`.
- Postgres does not index foreign keys automatically; every FK column is covered by an index
  or by the leading column of a unique constraint.
- Scores (`lead_score`, `score`, `confidence_score`) are integers 0–100.

## Entity overview

```
users 1─* campaigns 1─* campaign_leads *─1 businesses
                 1─* research_jobs *─0..1 businesses
businesses 1─* business_provider_identities | contacts | social_profiles
           1─* website_checks | prospect_sources | outreach_activities | suppression_list
```

Businesses are shared across campaigns (one row per real-world business). Ownership is via
campaigns: a user reaches a business only through one of their campaigns' leads.

## Tables

### campaigns
| Column | Type | Notes |
|---|---|---|
| user_id | FK users | cascade delete, indexed |
| name, location_query, country, category | varchar | required |
| city, state | varchar null | |
| target_count | uint | `CHECK >= 1`; MVP max 10 enforced in request validation |
| status | varchar | `CampaignStatus`, default `pending`, indexed |
| filters | jsonb | not null, default `{}` |
| started_at, completed_at | timestamp null | |

Indexed: `user_id`, `status`, `created_at`.
Status values: pending, discovering, researching, qualifying, completed, partially_completed, failed, cancelled.
Terminal: completed, partially_completed, failed, cancelled.

### businesses
| Column | Type | Notes |
|---|---|---|
| name, normalized_name | varchar | `normalized_name` indexed, **not unique** |
| category, city | varchar null | indexed |
| address | text null | |
| state, country, phone | varchar null | |
| latitude, longitude | decimal(10,7) null | range CHECKs |
| website_url | text null | |
| website_status | varchar | `WebsiteStatus`, default `unknown`, indexed |
| business_status | varchar | `BusinessStatus`, default `unknown` |
| lead_score | smallint null | `CHECK 0–100` |
| last_researched_at | timestamp null | |

Website status: unknown, has_website, social_only, no_website_found, inaccessible, uncertain.
Only `social_only` and `no_website_found` count as "no dedicated website" (`inaccessible` does not).

### business_provider_identities
`business_id` (cascade), `provider`, `external_id`. **Unique (provider, external_id)** — the
stable identifier used for cross-campaign deduplication. Storage subject to provider terms.

### contacts
`business_id` (cascade), `name` null, `email` (normalized), `role` null, `contact_type`
(`ContactType`), `verification_status` (`VerificationStatus`, default `unverified`),
`confidence_score` null (0–100), `source_url` null, `discovered_at`.
**Unique (business_id, email)**. Indexed on `email` for suppression lookups.

### social_profiles
`business_id` (cascade), `platform` (`SocialPlatform`), `profile_url`, `username` null,
`source_url` null, `confidence_score` null (0–100), `discovered_at`.
**Unique (business_id, platform, profile_url)**.

### website_checks
Append-only history. `business_id` (cascade), `checked_url` null, `status` (`WebsiteStatus`),
`http_status` null, `is_social_domain` (default false), `evidence` jsonb null, `checked_at`.
Indexed (business_id, checked_at).

### prospect_sources
Provenance per field. `business_id` (cascade), `provider`, `source_type`, `source_url` null,
`field_name` null, `evidence` jsonb null, `collected_at`. Indexed (business_id, field_name).

### campaign_leads
Pivot with its own id (`CampaignLead` model). `campaign_id`, `business_id` (both cascade),
`qualification_status` (`QualificationStatus`, default `pending`, indexed), `score` null (0–100),
`notes` null. **Unique (campaign_id, business_id)**.

### research_jobs
`campaign_id` (cascade), `business_id` null (**set null** on business delete, so job history
survives), `job_type` (`ResearchJobType`), `status` (`ResearchJobStatus`, default `pending`),
`attempts` (default 0), `error_message` null, `started_at`, `completed_at`.
Indexed (campaign_id, status).

### outreach_activities
`business_id` (cascade), `channel` (`OutreachChannel`), `status` (`OutreachStatus`),
`contacted_at` null, `notes` null. Indexed (business_id, created_at).

### suppression_list
`email` null (normalized, indexed), `business_id` null (**restrict** on delete), `reason`, `source`.
`CHECK (email IS NOT NULL OR business_id IS NOT NULL)`.
A contact is suppressed if its email **or** its business is listed (`Contact::suppressed()`,
`Contact::notSuppressed()`, `Contact::isSuppressed()`, `Business::isSuppressed()`,
`SuppressionEntry::coversEmail()`).

## Laravel framework tables

users, password_reset_tokens, sessions, cache, cache_locks, jobs, job_batches, failed_jobs,
personal_access_tokens (Sanctum).

## Seeding

`php artisan db:seed` runs `DemoDataSeeder` and refuses to run outside `local`/`testing`.
It creates `demo@prospectfinder.test` / `password` and 10 fictional businesses labelled
`[Demo]` with provider `demo`, using reserved `.example` domains and 555-01xx phone numbers.
