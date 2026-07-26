# Health Module — CLAUDE.md

Specifics of the `src/Health/` module. Global rules — in the root `CLAUDE.md`; navigation — in `CODEMAP.md`.

## Module responsibility

A liveness health check for orchestrators (Kubernetes/Compose/load balancers): `GET /health` → `200 {"status":"ok"}`. It is a plain (non-API-Platform) endpoint, deliberately kept outside the `/api` prefix, unauthenticated, and dependency-free so it answers even under load. Unlike `Example`, this module stays in a real project.

## Structure

- `Controller/HealthController.php` — a single invokable `#[AsController] final class` with `#[Route('/health', name: 'app_health', methods: ['GET'])]`, returning a `JsonResponse`. This is the sanctioned use of a controller (an atypical non-API-Platform endpoint) — no domain logic lives here.
- `di.php` — registers `App\Health\` (autowire/autoconfigure); autoconfigure tags the controller.
- `routing.php` — imports the module's attribute routes: `$routes->import(HealthController::class, 'attribute')`. Auto-loaded because `config/routes.php` globs `src/**/routing.php`.

## Tests

`tests/Integration/Health/HealthTest.php` extends Symfony's `WebTestCase` (not `App\Tests\ApiTestCase`) — the endpoint touches no database, so no schema reset is needed. Asserts `200`, `Content-Type: application/json`, and the `{"status":"ok"}` body.

## Notes / extension

- **Liveness only.** For a *readiness* probe (is the app able to serve — DB reachable, etc.), add a separate `GET /health/ready` that pings the connection (`Doctrine\DBAL\Connection::executeQuery('SELECT 1')`) and returns 503 on failure. Keep liveness dependency-free.
- **Test-env routing cache gotcha:** routes are imported via a glob (`src/**/routing.php`), and the test-env cache does not always rebuild that glob when a new module is added. After adding/moving a module route, run `bin/console cache:clear --env=test` once. CI is unaffected (fresh checkout = fresh cache).
