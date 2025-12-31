<?php

declare(strict_types=1);

namespace Mig\Actions;

use Mig\Support\Config;
use RuntimeException;

final readonly class CalculateRepeatableMigrationChecksum
{
    public function execute(string $fileName): string
    {
        $filePath = Config::repeatableMigrationsDirectoryPath().'/'.$fileName;
        $content = file_get_contents($filePath);

        if ($content === false) {
            throw new RuntimeException("Unable to read file $filePath");
        }

        return hash('sha256', $content);
    }
}
