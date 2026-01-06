<?php

declare(strict_types=1);

namespace Mig\Actions;

use Mig\Config;
use Mig\Database;

final readonly class RetrievePendingRepeatableMigrations
{
    public function __construct(
        private Database $db,
    ) {
        //
    }

    /**
     * @return string[]
     */
    public function execute(): array
    {
        $migrationsPath = Config::instance()->completeRepeatableMigrationsPath;
        $allMigrations = glob($migrationsPath.'/*.sql');

        if ($allMigrations === false || $allMigrations === []) {
            return [];
        }

        $allMigrations = array_map(fn ($file) => basename($file), $allMigrations);

        $statement = $this->db->pdo->prepare('select migration as file_name, checksum from mig_repeatable_migrations');
        $statement->execute();
        $result = $statement->fetchAll();

        $executedMigrations = array_column($result, 'checksum', 'file_name');

        $pendingMigrations = array_filter($allMigrations, function ($migrationName) use ($executedMigrations) {

            if (isset($executedMigrations[$migrationName])) {
                $checksum = new CalculateRepeatableMigrationChecksum()->execute($migrationName);
                if ($checksum === $executedMigrations[$migrationName]) {
                    return false;
                }
            }

            return true;
        });

        return $pendingMigrations;
    }
}
