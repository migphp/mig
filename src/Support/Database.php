<?php

declare(strict_types=1);

namespace Mig\Support;

use Mig\Config;
use PDO;

final class Database
{
    private static ?self $instance = null;

    public PDO $pdo;

    private function __construct(
        string $host,
        int $port,
        string $database,
        string $username,
        string $password,
    ) {
        $dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s', $host, $port, $database);

        $this->pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    public static function instance(): self
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        $dbConfig = Config::instance()->database;

        return self::$instance = new self(
            host: $dbConfig['host'],
            port: $dbConfig['port'],
            database: $dbConfig['database'],
            username: $dbConfig['username'],
            password: $dbConfig['password'],
        );
    }
}
