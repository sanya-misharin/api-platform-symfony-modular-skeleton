<!--
  Thanks for contributing! Keep the title in Conventional Commits form:
  <type>(<scope>): <subject>   e.g. feat(example): add status endpoint
  See CONTRIBUTING.md.
-->

## Summary

<!-- What does this PR do and why? One short paragraph. -->

## Type of change

<!-- Check all that apply. Should match the PR title type. -->

- [ ] feat — new feature
- [ ] fix — bug fix
- [ ] refactor — no behavior change
- [ ] perf — performance
- [ ] docs — documentation only
- [ ] test — tests only
- [ ] build / ci — tooling, deps, pipeline
- [ ] chore — other

## Related issue

<!-- e.g. Closes #123 -->

Closes #

## How it was tested

<!-- Commands run, scenarios covered, new/updated tests. -->

## Checklist

- [ ] Branch follows `<type>/<slug>` and commits follow Conventional Commits
- [ ] `docker compose exec -T php vendor/bin/phpstan analyse` is clean (level 6)
- [ ] `docker compose exec -T php vendor/bin/php-cs-fixer fix --dry-run --diff` is clean (`@Symfony`)
- [ ] `docker compose exec -T php vendor/bin/simple-phpunit` is green
- [ ] Doctrine migration generated if the schema changed (not applied — the entrypoint runs `migrate`)
- [ ] Documentation updated in both languages if user-facing (`README.md` + `docs/` and their `*.ru.md`)
- [ ] New/changed module has an up-to-date `src/<Module>/CLAUDE.md`
