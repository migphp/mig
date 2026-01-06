<?php

declare(strict_types=1);

namespace Mig;

use Mig\Support\ProjectPath;
use Mig\ValueObjects\DatabaseConfig;
use Symfony\Component\VarDumper\Cloner\Data;

final class Config
{
    public string $migrationsDirPath;

    public string $repeatableMigrationsDirPath;

    private const string JSON_CONFIGURATION_NAME = 'mig.json';

    private static ?self $instance = null;

    public function __construct(
        public string $migrationsRelativeDirPath,
        public DatabaseConfig $dbConfig,
    ) {
        $projectPath = ProjectPath::get();
        $this->migrationsDirPath = $projectPath.DIRECTORY_SEPARATOR.'migrations';
        $this->repeatableMigrationsDirPath = $this->migrationsDirPath.DIRECTORY_SEPARATOR.'repeatable';
    }

    public static function instance(
        ?string $overrideMigrationsRelativeDirPath = null,
        ?DatabaseConfig $overrideDbConfig = null,
    ): self {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        $configFilePath = ProjectPath::get().DIRECTORY_SEPARATOR.self::JSON_CONFIGURATION_NAME;

        $contents = file_exists($configFilePath)
            ? (string) file_get_contents($configFilePath)
            : '{}';

        /**
         * @var array{
         *     migrationsDir?: string,
         *  } $jsonAsArray
         */
        $jsonAsArray = json_decode($contents, true) ?: [];

        return self::$instance = new self(
            migrationsRelativeDirPath: $overrideMigrationsRelativeDirPath ?? $jsonAsArray['migrationsDir'] ?? 'migrations',
            dbConfig: $overrideDbConfig ?? DatabaseConfig::fromEnvironment(),
        );
    }

}
