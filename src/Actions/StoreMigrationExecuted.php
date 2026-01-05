<?php

namespace Mig\Actions;

use Mig\Support\Database;

final readonly class StoreMigrationExecuted
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::instance();
    }

    public function execute(string $fileName): void
    {
        $statement = $this->db->pdo->prepare('insert into mig_migrations (migration) values (?);');
        $statement->execute([$fileName]);
    }
}
