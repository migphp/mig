<?php

declare(strict_types=1);

namespace Mig;

use Mig\Support\ProjectPath;
use Mig\ValueObjects\DatabaseConfig;

final class Config
{
    public string $repeatableMigrationsDirPath;

    private const string JSON_CONFIGURATION_NAME = 'mig.json';

    private static ?self $instance = null;

    public function __construct(
        public string $migrationsDirPath,
        public DatabaseConfig $dbConfig,
    ) {
        $this->repeatableMigrationsDirPath = sprintf(
            "%s%s%s",
            $this->migrationsDirPath,
            DIRECTORY_SEPARATOR,
            self::JSON_CONFIGURATION_NAME,
        );
    }

    public static function instance(): self
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        $filePath = ProjectPath::get().DIRECTORY_SEPARATOR.self::JSON_CONFIGURATION_NAME;

        $contents = file_exists($filePath)
            ? (string) file_get_contents($filePath)
            : '{}';

        /**
         * @var array{
         *     migrationsDir?: string,
         *     database?: array{
         *         host: string,
         *         port: integer,
         *         name: string,
         *         username: string,
         *         password: string
         *     }
         *  } $jsonAsArray
         */
        $jsonAsArray = json_decode($contents, true) ?: [];

        return self::$instance = new self(
            migrationsDirPath: $jsonAsArray['migrationsDir'] ?? "migrations",
            dbConfig: new DatabaseConfig(
                host: $jsonAsArray['database']['host'] ?? "localhost",
                port: $jsonAsArray['database']['port'] ?? 5432,
                database: $jsonAsArray['database']['name'] ?? "postgres",
                username: $jsonAsArray['database']['username'] ?? "postgres",
                password: $jsonAsArray['database']['password'] ?? "postgres",
            )
        );
    }
}
