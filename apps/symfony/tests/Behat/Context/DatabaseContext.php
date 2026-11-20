<?php

declare(strict_types=1);

namespace App\Tests\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Hook\BeforeScenario;
use Doctrine\DBAL\Connection;

final class DatabaseContext implements Context
{
    private const MIGRATIONS_TABLE = 'doctrine_migration_versions';

    public function __construct(private readonly Connection $connection)
    {
    }

    #[BeforeScenario]
    public function truncateTables(): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $tables = [];

        foreach ($this->connection->createSchemaManager()->introspectTableNames() as $name) {
            if (self::MIGRATIONS_TABLE !== $name->getUnqualifiedName()->getValue()) {
                $tables[] = $name->toSQL($platform);
            }
        }

        if ([] === $tables) {
            return;
        }

        $this->connection->executeStatement(sprintf('TRUNCATE TABLE %s RESTART IDENTITY CASCADE', implode(', ', $tables)));
    }
}
