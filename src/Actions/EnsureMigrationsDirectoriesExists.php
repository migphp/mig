<?php

namespace Mig\Actions;

use Mig\Support\Config;

class EnsureMigrationsDirectoriesExists
{
    public function execute(): void
    {
        $this->ensureDirectoryExists(Config::migrationsDirectoryPath());
        $this->ensureDirectoryExists(Config::repeatableMigrationsDirectoryPath());
    }

    private function ensureDirectoryExists(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
    }
}
