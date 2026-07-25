# Spec — Test infrastructure

## Goal

Give the skeleton a working, self-contained test setup so the agent pipeline
(`tester` / `validator`) has something real to build on, and fix the defects that
prevented any integration test from running.

## Acceptance criteria

1. `docker compose exec -T php vendor/bin/simple-phpunit` boots the kernel and runs green.
2. `phpunit.xml.dist` points `KERNEL_CLASS` at the real kernel (`App\Kernel`) and the
   test `DATABASE_URL` at the Compose `database` host (reachable from inside the container).
3. Doctrine actually maps the modular entities (`App\<Module>\Entity`), so schema and
   IRIs work — `doctrine:schema:validate` is meaningful, not trivially empty.
4. A base `App\Tests\ApiTestCase` exists: it ensures the test database exists and resets
   the schema before every test.
5. An example API integration test (`tests/Integration/Example/ExampleTest.php`) covers the
   `Example` CRUD contract: collection, create (201), validation (422), read, update, delete
   (204 → 404).
6. A unit test example (`tests/Unit/Example/ExampleEntityTest.php`) exists.
7. `phpstan analyse` (level 6, includes `tests/`) is clean and `php-cs-fixer` reports no diff.

## Out of scope

- CI wiring that runs these gates on a PR (separate `ci/quality-gates` task).
- Test data factories / fixtures.
- Auth-related test helpers (the skeleton wires no concrete authentication).

## Notes / decisions

- **Root cause found:** the original `doctrine.yaml` only set `auto_mapping: true` with no
  `mappings`. For the modular `src/<Module>/Entity` layout (not `src/Entity`) that maps
  nothing, so `Example` was never a Doctrine entity — POST failed with "Unable to generate
  an IRI". Fixed with a single global mapping `App` → `src` in `config/packages/doctrine.php`,
  which covers every module (a new module needs no ORM config).
- `symfony/browser-kit` added to `require-dev` (needed by the API Platform test client).
- Tests run through `symfony/phpunit-bridge`, which installs PHPUnit under
  `vendor/bin/.phpunit/`. `phpstan-bootstrap.php` registers that install so PHPStan can
  resolve `PHPUnit\Framework\TestCase`. Run `vendor/bin/simple-phpunit install` once on a
  fresh checkout before PHPStan.
- Container CLI `memory_limit` raised to `512M` (`docker/frankenphp/conf.d/app.ini`) so
  `phpstan analyse` does not OOM while booting the container.
- Initial migration for the `examples` table generated (not applied — the entrypoint runs
  migrations on deploy).
