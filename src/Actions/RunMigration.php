<?php

declare(strict_types=1);

namespace Mig\Actions;

use Mig\Support\Database;

final readonly class RunMigration
{
    private Database $db;

    public function __construct(
        private string $fileName,
    ) {
        //
    }

    public function __invoke(): string
    {
        $sql = file_get_contents($this->fileName);
        if ($sql === false) {
            return 'fail to read file '.$this->fileName;
        }

        $result = $this->db->pdo()->exec($sql);

        if ($result === false) {
            // TODO: fix this ugliness
            var_dump($this->db->pdo()->errorInfo());
            return 'error: ';
        }

        $this->insertRunnedMigration($this->fileName);

        return 'success';
    }

    private function insertRunnedMigration(string $fileName): void
    {
        $statement = $this->db->pdo()->prepare('insert into mig_migrations (migration) values (?);');
        $statement->execute([$fileName]);
    }
}
