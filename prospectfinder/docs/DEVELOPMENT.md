# Development

## Local services

PostgreSQL and Redis must be running locally (default ports 5432 and 6379). On Debian/Ubuntu:

```bash
sudo service postgresql start
sudo service redis-server start
```

## Testing

- Backend tests run against the PostgreSQL database `prospectfinder_test` (configured in
  `backend/phpunit.xml`), not SQLite, so PostgreSQL-specific features (e.g. `jsonb`) are
  exercised faithfully. Tests use the array cache and sync queue.
- Frontend tests use Vitest with jsdom and React Testing Library (`src/test/setup.ts`).
- Playwright end-to-end tests are planned for Phase 16.

## Conventions

- PHP formatting: Laravel Pint (`vendor/bin/pint`).
- Frontend linting: ESLint flat config with `typescript-eslint` strict rules, React Hooks and
  React Refresh plugins.
- TypeScript: `strict` and `noUncheckedIndexedAccess` enabled.

## Environment files

- `backend/.env.example` — server configuration. Provider keys are added only as adapters land.
- `frontend/.env.example` — only `VITE_API_BASE_URL`. Anything `VITE_*` is public.
- Real `.env` files are git-ignored.
