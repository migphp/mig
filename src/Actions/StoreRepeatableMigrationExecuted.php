<?php

namespace Mig\Actions;

use Mig\Support\Database;

final readonly class StoreRepeatableMigrationExecuted
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::instance();
    }

    public function execute(string $fileName): void
    {
        $checksum = new CalculateRepeatableMigrationChecksum()->execute($fileName);
        $statement = $this->db->pdo->prepare(<<<SQL
            insert into mig_repeatable_migrations (migration, checksum) values (?, ?)
            on conflict (migration) do update set checksum = ?;
SQL
        );

        $statement->execute([$fileName, $checksum, $checksum]);
    }
}
