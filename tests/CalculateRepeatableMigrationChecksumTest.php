<?php

declare(strict_types=1);

use Mig\Actions\CalculateRepeatableMigrationChecksum;
use Mig\Config;

beforeAll(function (): void {
    Config::instance(overrideMigrationsPath: 'tests/stubs');
});

it('calculates checksum for simple table creation', function (): void {
    $action = new CalculateRepeatableMigrationChecksum;

    $result = $action->execute('simple_migration.sql');

    $fileContent = file_get_contents(__DIR__.'/stubs/repeatable/simple_migration.sql');

    $expectedHash = hash('sha256', (string) $fileContent);

    expect($result)->toBe($expectedHash);
});

it('throws exception when migration file does not exist', function (): void {
    $action = new CalculateRepeatableMigrationChecksum;

    $action->execute('nonexistent.sql');

})->throws(RuntimeException::class);

it('throws exception when migration file cannot be read', function (): void {
    $testFile = __DIR__.'/stubs/repeatable/unreadable.sql';

    // Create a test file
    file_put_contents($testFile, 'test content');

    // Remove read permissions
    chmod($testFile, 0000);

    // Suppress PHP warnings for this test
    set_error_handler(function (): bool {
        return true;
    });

    try {
        $action = new CalculateRepeatableMigrationChecksum;
        $action->execute('unreadable.sql');
    } finally {
        restore_error_handler();
        // Restore permissions and clean up
        chmod($testFile, 0644);
        unlink($testFile);
    }
})->throws(RuntimeException::class);

it('calculates checksum for empty migration file', function (): void {
    $action = new CalculateRepeatableMigrationChecksum;

    $result = $action->execute('empty_migration.sql');

    expect($result)->toBe(hash('sha256', ''));
});

it('calculates checksum for complex migration with multiple statements', function (): void {
    $action = new CalculateRepeatableMigrationChecksum;
    $result = $action->execute('complex_migration.sql');

    $expectedContent = file_get_contents(__DIR__.'/stubs/repeatable/complex_migration.sql');

    $expectedHash = hash('sha256', (string) $expectedContent);

    expect($result)->toBe($expectedHash);
});
