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
        $tables = array_filter(
            $this->connection->createSchemaManager()->listTableNames(),
            static fn (string $table): bool => self::MIGRATIONS_TABLE !== $table,
        );

        if ([] === $tables) {
            return;
        }

        $quotedTables = array_map($this->connection->quoteSingleIdentifier(...), $tables);

        $this->connection->executeStatement(sprintf('TRUNCATE TABLE %s RESTART IDENTITY CASCADE', implode(', ', $quotedTables)));
    }
}
