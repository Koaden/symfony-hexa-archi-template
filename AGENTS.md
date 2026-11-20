# AGENTS.md

This file provides guidance to coding agents working in this repository.

## Layout

Monorepo: root holds Docker/Compose/makefile, the Symfony app (PHP 8.4, FrankenPHP, PostgreSQL) lives in `apps/symfony`. Everything runs in containers through the root `makefile`; there is no local PHP. Config comes from the root `.env` (copy `.env.dist`), there is no app-level `.env`.

## Commands (run from the repo root)

```bash
make dev                  # start dev stack
make stop                 # stop all stacks
make test                 # phpunit + behat
make phpunit
make behat
make lint                 # phpstan (max, strict and deprecation rules) + php-cs-fixer dry run + deptrac + lint.rules
make lint.rules           # project rules a linter cannot express (no phpstan ignore, migration docblock, translation keys, Domain exceptions)
make infection            # mutation testing on the Domain
make database.migration.generate
make database.migrate
```

## Architecture

Hexagonal under `apps/symfony/src` (`App\`), layer rules enforced by Deptrac (`lint/deptrac/deptrac.yaml`):

- `Domain`: framework-free (no Symfony or Doctrine imports). Models, value objects, enums, domain events, repository and service ports, and use cases.
- `Application`: HTTP controllers, forms, security glue, message bus ports (`CommandBus`, `QueryBus`, `EventBus`).
- `Infrastructure`: adapters (Doctrine repositories, Messenger buses, mail, security, storage).

Use cases are CQRS: `Domain/UseCase/{Command,Query}/<Name>` holds the command or query and its `Handler`. Handlers have no Symfony attributes: they are tagged per bus in `config/services/use_cases.php`. Controllers inject the bus ports, never handlers (Deptrac enforces it). Domain events are collected on the model, then dispatched by the `DomainEventDispatcher` Messenger middleware after the handler runs.

Doctrine mapping is XML in `src/Infrastructure/Doctrine/ORM`, file named after the class, entities are plain PHP objects. Symfony config is PHP files in `config/`, not YAML. Translation catalogs are nested YAML with lowercase snake_case keys.

Front: Twig + HTMX + Bootstrap SCSS via AssetMapper (no Node).

## Code rules

- Never use `@phpstan-ignore` or `ignoreErrors`. Fix the code instead (`make lint.rules` fails on it).
- Classes are `final` by default, `declare(strict_types=1)` everywhere.
- Few comments: unclear code is a code problem.
- Domain failures throw a dedicated exception class, never a generic one.

## Tests

- PHPUnit (`tests/PHPUnit`) covers the Domain only, with in-memory fakes and builders, no kernel or database. Use a mock only when a fake does not fit (checking an interaction or a side effect).
- Behat (`tests/Behat/features`, single `FeatureContext`) covers the UI, one feature file per CRUD.
- Mutation testing (`make infection`) guards the quality of the Domain tests, its thresholds live in `lint/infection/infection.json5`.
- Run only the target of a change (one test, one feature), the full suite before a PR.

## Workflow

Think like a senior developer who will maintain this code for years: working code is not enough, it must be simple, readable and in the right place.

1. **Design first.** Beyond a local change, state in a few lines the need, the layers touched, the ports to create or reuse and where each responsibility lives. Reuse and extend existing code before creating new code, unify duplication instead of copying it.
2. **Test first for business rules.** Write the failing PHPUnit test (Domain) or Behat scenario (UI), have the behavior validated, then implement.
3. **Smallest change that solves the problem.** No scope creep, no speculative abstraction. Use a design pattern (Strategy, Factory, Decorator, Specification, domain events...) only when it solves a present problem, and name it. Never trade the architecture for a shortcut.
4. **Review your own diff before handing back** as a demanding reviewer would: layers respected, duplication, edge cases, leftover debug, naming. Fix first, then run the targeted checks.
