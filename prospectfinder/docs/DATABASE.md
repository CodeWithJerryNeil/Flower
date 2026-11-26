# Database

PostgreSQL 16. Development database `prospectfinder`; test database `prospectfinder_test`.

## Current tables (Phase 0, Laravel defaults)

| Migration | Tables |
|-----------|--------|
| `0001_01_01_000000_create_users_table` | users, password_reset_tokens, sessions |
| `0001_01_01_000001_create_cache_table` | cache, cache_locks |
| `0001_01_01_000002_create_jobs_table` | jobs, job_batches, failed_jobs |
| `*_create_personal_access_tokens_table` | personal_access_tokens (Sanctum) |

The domain schema (campaigns, businesses, contacts, etc.) is introduced in Phase 1 and will be
documented here as it is implemented.
