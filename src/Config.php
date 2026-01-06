<?php

declare(strict_types=1);

namespace Mig;

use Mig\Support\ProjectPath;
use Mig\ValueObjects\DatabaseConfig;

final class Config
{
    public string $completeMigrationsPath;

    public string $completeRepeatableMigrationsPath;

    private const string JSON_CONFIGURATION_NAME = 'mig.json';

    private static ?self $instance = null;

    public function __construct(
        public string $migrationsPath,
        public DatabaseConfig $dbConfig,
    ) {
        $projectPath = ProjectPath::get();
        $this->completeMigrationsPath = $projectPath.DIRECTORY_SEPARATOR.$migrationsPath;
        $this->completeRepeatableMigrationsPath = $this->completeMigrationsPath.DIRECTORY_SEPARATOR.'repeatable';
    }

    public static function instance(
        ?string $overrideMigrationsPath = null,
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
            migrationsPath: $overrideMigrationsPath ?? $jsonAsArray['migrationsDir'] ?? 'migrations',
            dbConfig: $overrideDbConfig ?? DatabaseConfig::fromEnvironment(),
        );
    }

    public function discard(): void
    {
        self::$instance = null;
    }
}
