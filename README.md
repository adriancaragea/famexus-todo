# famexus-todo

Famexus Todo service. Family todo lists: lists, items, assignees and due dates. Only reachable from the gateway over the Docker network.

Laravel 13 on PHP 8.5, MySQL 8 (database `famexus_todo`), Redis for queues and cache.

## Run it

The usual way is through [famexus-infra](../famexus-infra), which starts every service, MySQL, Redis and Mailpit with docker-compose.

To run this service on its own:

```sh
composer setup          # composer install, copy .env, generate key, migrate
composer dev            # serves on http://localhost:8001
```

Without the infra stack you need MySQL and Redis yourself, or set `DB_CONNECTION=sqlite` and `QUEUE_CONNECTION=sync` / `CACHE_STORE=file` in `.env`.

Check it is up:

```sh
curl http://localhost:8001/up
curl http://localhost:8001/api/v1/ping
```

`/api/v1/hello` only answers requests carrying a gateway-signed JWT, so try it through the gateway: `curl http://localhost:8000/api/v1/hello`.

## JWT keys

Requests arrive from the gateway with a short-lived RS256 JWT. Put the gateway's public key at `storage/keys/jwt-public.pem` (see the gateway README for how to generate it). Keys are git-ignored.

## Quality

```sh
composer test           # Pest
composer lint           # Pint (check only) + PHPStan level 6 via Larastan
composer format         # Pint, fixes formatting
```

GitHub Actions runs the same checks on every push to `main` and every pull request (`.github/workflows/ci.yml`).

## API

REST/JSON under `/api/v1`. The OpenAPI spec lives in [`docs/openapi.yaml`](docs/openapi.yaml); keep it in step with the routes.

## Roadmap

- [ ] JWT verification middleware (gateway public key) that sets the current user and family
- [ ] Lists and items, scoped by `family_id`
- [ ] Assignees, due dates, completion
- [ ] Permissions: `todo.view`, `todo.edit`
