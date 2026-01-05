<?php

namespace Mig\Actions;

use Mig\Config;

class EnsureMigrationsDirectoriesExists
{
    public function execute(): void
    {
        $config = Config::instance();

        $this->ensureDirectoryExists($config->migrationsDirPath);
        $this->ensureDirectoryExists($config->repeatableMigrationsDirPath);
    }

    private function ensureDirectoryExists(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
    }
}
