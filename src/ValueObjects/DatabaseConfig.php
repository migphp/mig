<?php

declare(strict_types=1);

namespace Mig\ValueObjects;

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
        $databaseUrl = $_ENV['DB_URL'];

        if ($databaseUrl) {
            $url = parse_url($databaseUrl);

            return new self(
                host: $url['host'],
                port: $url['port'],
                database: $url['path'],
                username: $url['user'],
                password: $url['pass'],
            );
        }

        return new self(
            host: $_ENV['DB_HOST'],
            port: $_ENV['DB_PORT'],
            database: $_ENV['DB_DATABASE'],
            username: $_ENV['DB_USERNAME'],
            password: $_ENV['DB_PASSWORD'],
        );
    }
}
