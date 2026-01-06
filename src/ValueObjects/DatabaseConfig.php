<?php

declare(strict_types=1);

namespace Mig\ValueObjects;

use Dotenv\Dotenv;
use Mig\Support\ProjectPath;

final readonly class DatabaseConfig
{
    public function __construct(
        public string $host,
        public int $port,
        public string $database,
        public string $username,
        public string $password,
    ) {
        //
    }

    public static function fromEnvironment(): self
    {
        $dotenv = Dotenv::createImmutable(ProjectPath::get());
        $dotenv->load();

        $databaseUrl = $_ENV['DB_URL'] ?? null;

        if ($databaseUrl) {
            $url = parse_url($databaseUrl);

            return new self(
                host: $url['host'],
                port: (int) $url['port'],
                database: ltrim($url['path'], '/'),
                username: $url['user'],
                password: $url['pass'],
            );
        }

        return new self(
            host: $_ENV['DB_HOST'] ?? 'localhost',
            port: (int) ($_ENV['DB_PORT'] ?? 5432),
            database: $_ENV['DB_DATABASE'] ?? 'postgres',
            username: $_ENV['DB_USERNAME'] ?? 'postgres',
            password: $_ENV['DB_PASSWORD'] ?? 'postgres',
        );
    }
}
