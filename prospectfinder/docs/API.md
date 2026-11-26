# API

Base URL: `/api/v1`. All responses are JSON.

Planned envelope (formalised in Phase 2):

```json
{ "success": true,  "message": "…", "data": {} }
{ "success": false, "message": "…", "errors": {} }
```

## Endpoints

### `GET /health` — public

Reports reachability of the database and Redis. Returns booleans only, never connection details.

- `200` — all checks pass
- `503` — one or more checks fail

```json
{
  "success": true,
  "message": "All systems operational.",
  "data": { "checks": { "database": true, "redis": true } }
}
```

Laravel's framework liveness route `GET /up` is also available (HTML, outside `/api/v1`).
