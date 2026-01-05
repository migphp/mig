<?php

declare(strict_types=1);

namespace Mig;

use Mig\Support\ProjectPath;

final class Config
{
    public string $repeatableMigrationsDirPath;

    private const string JSON_CONFIGURATION_NAME = 'mig.json';

    private static ?self $instance = null;

    /**
     * @param  array{
     *             host: string,
     *             port: integer,
     *             database: string,
     *             username: string,
     *             password: string
     * }  $database
     */
    public function __construct(
        public string $migrationsDirPath,
        public array $database,
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
         *         database: string,
         *         username: string,
         *         password: string
         *     }
         *  } $jsonAsArray
         */
        $jsonAsArray = json_decode($contents, true) ?: [];

        return self::$instance = new self(
            migrationsDirPath: $jsonAsArray['migrationsDir'] ?? "migrations",
            database: [
                'host' => $jsonAsArray['database']['host'] ?? 'localhost',
                'port' => $jsonAsArray['database']['port'] ?? 5432,
                'database' => $jsonAsArray['database']['database'] ?? 'postgres',
                'username' => $jsonAsArray['database']['username'] ?? 'postgres',
                'password' => $jsonAsArray['database']['password'] ?? 'postgres',
            ],
        );
    }
}
