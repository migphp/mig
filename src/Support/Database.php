<?php

declare(strict_types=1);

namespace Mig\Support;

use PDO;
use RuntimeException;

final class Database
{
    private static ?self $instance = null;

    private ?PDO $pdo = null;

    private function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly string $database,
        private readonly string $username,
        private readonly string $password,
    ) {
        //
    }

    public static function connect(): self
    {
        if (self::$instance === null) {
            self::$instance = self::fromEnvironment();
        }

        return self::$instance;
    }

    public function pdo(): PDO
    {
        if ($this->pdo === null) {
            $dsn = sprintf(
                'pgsql:host=%s;port=%d;dbname=%s',
                $this->host,
                $this->port,
                $this->database
            );

            $this->pdo = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }

        return $this->pdo;
    }

    private static function fromEnvironment(): self
    {
        // Try DATABASE_URL first
        $databaseUrl = getenv('DATABASE_URL');
        if ($databaseUrl !== false && $databaseUrl !== '') {
            return self::fromDatabaseUrl($databaseUrl);
        }

        // Fall back to individual environment variables
        $host = getenv('DB_HOST');
        $port = getenv('DB_PORT');
        $database = getenv('DB_DATABASE');
        $username = getenv('DB_USERNAME');
        $password = getenv('DB_PASSWORD');

        if ($host === false || $database === false || $username === false || $password === false) {
            throw new RuntimeException(
                'Database configuration not found. Set DATABASE_URL or DB_HOST, DB_DATABASE, DB_USERNAME, and DB_PASSWORD environment variables.'
            );
        }

        return new self(
            host: $host,
            port: $port !== false ? (int) $port : 5432,
            database: $database,
            username: $username,
            password: $password,
        );
    }

    private static function fromDatabaseUrl(string $url): self
    {
        $parsed = parse_url($url);

        if ($parsed === false || !isset($parsed['scheme'], $parsed['host'], $parsed['user'], $parsed['pass'])) {
            throw new RuntimeException(
                'Invalid DATABASE_URL format. Expected: pgsql://user:pass@host:port/dbname'
            );
        }

        $database = isset($parsed['path']) ? ltrim($parsed['path'], '/') : '';
        if ($database === '') {
            throw new RuntimeException(
                'Database name missing in DATABASE_URL. Expected: pgsql://user:pass@host:port/dbname'
            );
        }

        return new self(
            host: $parsed['host'],
            port: $parsed['port'] ?? 5432,
            database: $database,
            username: $parsed['user'],
            password: $parsed['pass'],
        );
    }
}
