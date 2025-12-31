<?php

declare(strict_types=1);

namespace Mig\Support;

use RuntimeException;

final class Config
{
    private static ?array $config = null;

    public static function migrationsDirectoryPath(): string
    {
        $config = self::load();
        $migrationsPath = $config['migrations_directory_path'] ?? 'migrations';

        return sprintf("%s/%s", ProjectPath::get(), $migrationsPath);
    }

    public static function repeatableMigrationsDirectoryPath(): string
    {
        return self::migrationsDirectoryPath().'/repeatable';
    }

    private static function load(): array
    {
        if (self::$config !== null) {
            return self::$config;
        }

        $configPath = ProjectPath::get().'/mig.json';

        if (! file_exists($configPath)) {
            throw new RuntimeException(
                "Config file not found. Create mig.json in project root with:\n".
                '{"migrations_directory_path": "migrations"}'
            );
        }

        $json = file_get_contents($configPath);

        if ($json === false) {
            throw new RuntimeException('Unable to read mig.json file.');
        }

        $config = json_decode($json, true);

        if ($config === null) {
            throw new RuntimeException(
                'Invalid JSON in mig.json. Error: '.json_last_error_msg()
            );
        }

        self::$config = $config;

        return self::$config;
    }
}
