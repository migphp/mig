<?php

declare(strict_types=1);

namespace Mig\Actions;

use Mig\Database;

final readonly class EnsureMigrationsTablesExists
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::instance();
    }

    public function execute(): void
    {
        $this->migrationsTable();
        $this->repeatableMigrationsTable();
    }

    private function migrationsTable(): void
    {
        $statement = $this->db->pdo->prepare(
            <<<SQL
                create table if not exists mig_migrations (
                    id bigint generated always as identity primary key,
                    migration varchar(317) not null,
                    executed_at timestamp with time zone default current_timestamp
                )
SQL
        );
        $statement->execute();
    }

    private function repeatableMigrationsTable(): void
    {
        $statement = $this->db->pdo->prepare(
            <<<SQL
            create table if not exists mig_repeatable_migrations (
                id bigint generated always as identity primary key,
                migration varchar(304) not null unique,
                checksum varchar(64) not null,
                executed_at timestamp with time zone default current_timestamp
            )
SQL
        );
        $statement->execute();
    }
}
