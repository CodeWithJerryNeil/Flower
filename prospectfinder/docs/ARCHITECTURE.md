# Architecture

## Overview

```
Browser ── React SPA (frontend/, Vite) ──REST /api/v1──► Laravel API (backend/)
                                                           ├── PostgreSQL (source of truth)
                                                           ├── Redis (queues, cache)
                                                           └── Queue workers ──► external providers (future phases)
```

## Current state (Phase 0)

- `backend/`: Laravel 13 with Sanctum installed, API routes under `/api/v1`
  (configured via `apiPrefix` in `bootstrap/app.php`), CORS restricted to `FRONTEND_URL`.
  Only endpoint: `GET /api/v1/health`.
- `frontend/`: React 19 + TypeScript (strict) + Tailwind 4. A single status page calls the
  health endpoint through an Axios client (`src/lib/api.ts`).

## Planned layering (backend)

| Layer | Responsibility |
|-------|----------------|
| Form Requests | Input validation |
| Controllers | Thin HTTP adapters |
| API Resources | Response shaping (no raw models) |
| Policies | Ownership/authorization |
| Services | Business logic (discovery, normalization, website detection, research, scoring) |
| Contracts + adapters | External providers behind interfaces |
| Jobs | Asynchronous research pipeline orchestrated per campaign |

Components above are planned, not yet implemented. See IMPLEMENTATION_STATUS.md.
