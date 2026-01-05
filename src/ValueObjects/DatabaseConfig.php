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
}
