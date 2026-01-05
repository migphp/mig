<?php

declare(strict_types=1);

namespace Mig\Actions;

use Mig\Support\Database;

final readonly class RunMigration
{
    private Database $db;

    public function __construct(
    ) {
        $this->db = Database::instance();
    }

    /**
     * @return array{0:bool, 1:string}
     */
    public function execute(
        string $filePath,
    ): array
    {
        $sql = file_get_contents($filePath);
        if ($sql === false) {
            return [false, "fail to read file $filePath"];
        }

        if (trim($sql) === '') {
            return [false, '🤨 Empty migration, skipping.'];
        }

        $result = $this->db->pdo->exec($sql);

        if ($result === false) {
            // TODO: fix this ugliness
            var_dump($this->db->pdo->errorInfo());
            return [false, 'error: '];
        }

        return [true, 'success'];
    }
}
