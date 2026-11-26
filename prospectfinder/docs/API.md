# API

Base URL: `/api/v1`. All responses are JSON, even when the client omits `Accept`.

## Envelope

```json
{ "success": true,  "message": "Request completed.", "data": {} }
{ "success": false, "message": "Validation failed.", "errors": { "field": ["…"] } }
```

| Status | Message | When |
|---|---|---|
| 401 | `Unauthenticated.` | Missing, invalid or expired token |
| 404 | `Resource not found.` | Unknown route, missing record, **or a record owned by another user** |
| 405 | `Method not allowed.` | |
| 422 | `Validation failed.` | `errors` holds per-field messages |
| 429 | `Too many requests. Please try again later.` | Includes `Retry-After` header |
| 500 | `Server error.` | No internal details (when `APP_DEBUG=false`) |

## Authentication

Sanctum **bearer tokens**: `Authorization: Bearer <token>`. Tokens expire after
`SANCTUM_TOKEN_EXPIRATION` minutes (default 7 days); expired tokens are pruned daily by the
scheduler. Session cookies are not accepted on API routes, so no CSRF token is needed.

There is **no registration endpoint**. Provision accounts on the server:

```bash
php artisan prospectfinder:create-user you@example.com --name="Your Name"   # prompts for password (min 12)
```

### `POST /auth/login` — public, 5 attempts/min per email+IP

Request: `{ "email": "…", "password": "…", "device_name": "optional" }`

`200`:
```json
{
  "success": true,
  "message": "Logged in.",
  "data": {
    "token": "1|…",
    "token_type": "Bearer",
    "expires_at": "2026-12-03T06:52:45+00:00",
    "user": { "id": 1, "name": "…", "email": "…", "created_at": "…" }
  }
}
```
`422` with `errors.email = ["These credentials do not match our records."]` for any bad
credential (same response for unknown email and wrong password).

### `POST /auth/logout` — auth
Revokes the token used for the request only. `200`, `data: null`.

### `GET /auth/me` — auth
`200`, `data` = user (`id`, `name`, `email`, `created_at`).

## Rate limits
- All API routes: 120 requests/min per user (or IP when unauthenticated).
- Login: 5 attempts/min per email + IP.

## `GET /health` — public
`200` when database and Redis are reachable, `503` otherwise.
`data.checks = { "database": bool, "redis": bool }`. Laravel's `GET /up` also exists (HTML).

## Validation contracts (Form Requests — endpoints arrive in Phase 8)

### StoreCampaignRequest
| Field | Rules |
|---|---|
| name | required, ≤255 |
| location | required object, keys: query, city, state, country |
| location.city / location.query | at least one required |
| location.country | required |
| category | required, ≤120 |
| target_count | required integer 1–10 (`prospectfinder.campaigns.max_target_count`) |
| filters | optional object, keys: website_status, prioritize_email |
| filters.website_status[] | website status values, distinct |
| filters.prioritize_email | boolean |
| research_mode | optional, only `automatic` (not stored; the only mode) |

`location_query` is the free-text `query` or, if absent, `"city, state, country"`.

### UpdateCampaignRequest
Same fields, all optional. Once a campaign has left `pending`, only `name` may change;
other fields return 422 `"Campaign settings can only be changed before the campaign starts."`

### UpdateLeadRequest (lead = campaign lead)
`qualification_status` (pending|qualified|needs_review|rejected), `notes` (≤5000, nullable),
`business` object limited to: name, category, address, city, state, country,
phone (digits, spaces, `+().-`), website_url (http/https only), website_status.

### StoreOutreachActivityRequest
`channel` (email|facebook|instagram|phone|other), `status`
(planned|contacted|replied|interested|not_interested|won|lost), `contacted_at` (not in the
future), `notes` (≤5000). If the business is suppressed, `planned` and `contacted` are rejected;
replies and outcomes can still be logged.

## Resources (response shapes)

- **User**: id, name, email, created_at
- **Campaign**: id, name, location{query, city, state, country}, category, target_count,
  status, filters, leads_count (when counted), started_at, completed_at, created_at, updated_at.
  `user_id` is not exposed.
- **Business**: id, name, category, address, city, state, country, latitude, longitude, phone,
  website_url, website_status, business_status, lead_score, last_researched_at, created_at,
  updated_at, plus when loaded: contacts, social_profiles, website_checks, sources.
  `normalized_name` and provider identities are not exposed.
- **Contact**: id, name, email, role, contact_type, verification_status, confidence_score,
  source_url, discovered_at
- **SocialProfile**: id, platform, profile_url, username, source_url, confidence_score, discovered_at
- **WebsiteCheck**: id, checked_url, status, http_status, is_social_domain, evidence, checked_at
- **ProspectSource**: id, provider, source_type, source_url, field_name, evidence, collected_at

`evidence` and URLs are third-party data: clients must render them as text, never as HTML.

## Authorization
Users access only their own campaigns, the leads in them, and businesses reachable through
them. Ownership is derived from the authenticated user, never from request input.
Denials are returned as **404**, so other users' IDs cannot be probed.
