<?php

declare(strict_types=1);

namespace Mig;

use Mig\ValueObjects\DatabaseConfig;
use PDO;

final class Database
{
    private static ?self $instance = null;

    public PDO $pdo;

    private function __construct(
        DatabaseConfig $config,
    ) {
        $dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s', $config->host, $config->port, $config->database);

        $this->pdo = new PDO($dsn, $config->username, $config->password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    public static function instance(): self
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        return self::$instance = new self(Config::instance()->dbConfig);
    }
}
