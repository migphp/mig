<?php

declare(strict_types=1);

namespace Mig\Actions;

use Mig\Config;
use Mig\Database;

final readonly class RetrievePendingMigrations
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
        $migrationsPath = Config::instance()->migrationsDirPath;
        $allFiles = glob($migrationsPath.'/*.sql');
        $fileNames = array_map(fn($file) => basename($file), $allFiles);

        if ($allFiles === false || $allFiles === []) {
            return [];
        }

        $statement = $this->db->pdo->prepare('select migration from mig_migrations');
        $statement->execute();
        $result = $statement->fetchAll();

        $alreadyMigratedList = array_column($result, 'migration');
        $pendingMigrations = array_diff($fileNames, $alreadyMigratedList);

        return $pendingMigrations;
    }
}
