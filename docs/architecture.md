# Architecture

The application uses a ports-and-adapters layout under the `App\` namespace.

```text
src/
├── Domain/
│   ├── Data/
│   │   ├── Model/      Entities and value objects
│   │   └── Collection/ Domain collections (repository ports)
│   ├── Event/          Domain events and the events registerer
│   ├── ReadModel/      Query result objects
│   ├── Repository/     Repository ports (interfaces)
│   └── UseCase/
│       ├── Command/    Commands and their handlers
│       └── Query/      Queries and their handlers
├── Application/
│   ├── Http/Controller/ HTTP adapters
│   └── MessageBus/     Command, query and event bus ports
└── Infrastructure/
    ├── Doctrine/
    │   ├── DataFixtures/
    │   ├── Migrations/
    │   ├── ORM/        Doctrine XML mappings
    │   └── Repository/ Repository adapters
    └── Symfony/Messenger/
        ├── CommandBus.php
        ├── QueryBus.php
        ├── EventBus.php
        └── Middleware/DomainEventDispatcher.php
```

Configuration is PHP files under `config/` (packages, services, routes). YAML is not used.

## Dependency rules

| Layer | May depend on |
| --- | --- |
| Domain | Nothing outside PHP |
| Application | Domain, Symfony |
| Infrastructure | Application, Domain, Symfony, Doctrine |
| Kernel | Infrastructure, Symfony |

`deptrac.yaml` enforces these rules. `make deptrac` fails the build when a
layer points in the wrong direction.

Domain code is framework-free: no Symfony or Doctrine imports. Doctrine
metadata lives in XML outside the domain classes.

## Request flow

A command follows this path:

```text
HTTP controller (Application)
  -> CommandBus (Application port)
  -> Symfony Messenger command.bus
  -> CommandHandler
  -> Domain model / repository port
  -> Domain events registered
  -> DomainEventDispatcher middleware
  -> EventBus
  -> Event handlers
```

Queries use `query.bus` and return read models. Domain events are dispatched by
the command bus after the command handler runs; event handlers must tolerate
being called after the Doctrine transaction middleware has committed.

Messenger buses are configured in `config/packages/messenger.php`.
Handlers are tagged by bus in `config/services/use_cases.php`; they do not need
Symfony attributes.

## Doctrine XML mappings

Mappings are XML files in `src/Infrastructure/Doctrine/ORM`, loaded with the
`App\Domain\Data\Model` prefix:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
                  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                  xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping
                                      https://www.doctrine-project.org/schemas/orm/doctrine-mapping.xsd">
    <entity name="App\Domain\Data\Model\Product" table="product">
        <id name="id" type="integer" column="id">
            <generator strategy="IDENTITY"/>
        </id>
        <field name="name" type="string" column="name" length="255"/>
    </entity>
</doctrine-mapping>
```

The file must be named after the class, for example `Product.orm.xml`, and the
`name` attribute must contain the full class name. The entity class is a plain
PHP object in `src/Domain/Data/Model`; it has no Doctrine attributes.

The mapping configuration is in `config/packages/doctrine.php`. Migrations live
in `src/Infrastructure/Doctrine/Migrations`.

## Adding a use case

1. Create the command or query in `src/Domain/UseCase/{Command,Query}`.
2. Add its `Handler` class next to it. Handlers are discovered by
   `config/services/use_cases.php` and tagged for the right bus.
3. Put domain behaviour in `App\Domain` and inject repository ports; implement
   those ports under `App\Infrastructure\Doctrine\Repository`. Most use cases
   belong in the domain; keep `App\Application` for orchestration that does not
   fit the domain.
4. Add an HTTP controller under `src/Application/Http/Controller` and inject
   the `CommandBus` or `QueryBus` port.
5. If the domain registers events, add event classes under `App\Domain\Event`
   and handlers under `App\Domain`, `App\Application` or `App\Infrastructure`.

## Tests and checks

```bash
make test        # PHPUnit
make deptrac # Deptrac layer rules
make lint        # PHPStan (max), PHP-CS-Fixer, Deptrac
```

PHPUnit tests live under `tests/PHPUnit`:

```text
tests/
└── PHPUnit/
    └── Domain/Event/EventsRegistererTest.php
```

The suite is declared in `phpunit.dist.xml`. The PHPUnit result cache lives in
`tests/PHPUnit/.phpunit.cache` and is ignored by Git and Docker.

Tests use the container environment. There are no app-level `.env` files;
configuration comes from the root `.env` through Compose.
