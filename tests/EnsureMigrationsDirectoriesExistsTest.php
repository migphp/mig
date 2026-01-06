<?php

declare(strict_types=1);

use Mig\Actions\EnsureMigrationsDirectoriesExists;
use Mig\Config;

beforeEach(function (): void {
    Config::reset();
});

afterEach(function (): void {
    $testDirs = [
        __DIR__.'/temp/test-migrations',
        __DIR__.'/temp',
        __DIR__.'/deep/nested/path/migrations',
        __DIR__.'/deep/nested/path',
        __DIR__.'/deep/nested',
        __DIR__.'/deep',
    ];

    foreach ($testDirs as $dir) {
        if (is_dir($dir)) {
            $repeatableDir = $dir.'/repeatable';
            if (is_dir($repeatableDir)) {
                rmdir($repeatableDir);
            }
            rmdir($dir);
        }
    }
});

it('creates main migrations directory when it does not exist', function (): void {
    $testPath = 'tests/temp/test-migrations';
    $fullPath = __DIR__.'/temp/test-migrations';
    $repeatablePath = $fullPath.'/repeatable';

    if (is_dir($repeatablePath)) {
        rmdir($repeatablePath);
    }
    if (is_dir($fullPath)) {
        rmdir($fullPath);
    }

    Config::instance(overrideMigrationsPath: $testPath);

    $action = new EnsureMigrationsDirectoriesExists;
    $action->execute();

    expect(is_dir($fullPath))->toBeTrue()
        ->and(is_dir($repeatablePath))->toBeTrue();
});

it('creates repeatable migrations directory when main directory exists but repeatable does not', function (): void {
    $testPath = 'tests/temp/test-migrations';
    $fullPath = __DIR__.'/temp/test-migrations';
    $repeatablePath = $fullPath.'/repeatable';

    if (! is_dir($fullPath)) {
        mkdir($fullPath, 0777, true);
    }
    if (is_dir($repeatablePath)) {
        rmdir($repeatablePath);
    }

    Config::instance(overrideMigrationsPath: $testPath);

    $action = new EnsureMigrationsDirectoriesExists;
    $action->execute();

    expect(is_dir($fullPath))->toBeTrue()
        ->and(is_dir($repeatablePath))->toBeTrue();
});

it('does not error when both directories already exist', function (): void {
    $testPath = 'tests/temp/test-migrations';
    $fullPath = __DIR__.'/temp/test-migrations';
    $repeatablePath = $fullPath.'/repeatable';

    if (! is_dir($repeatablePath)) {
        mkdir($repeatablePath, 0777, true);
    }

    Config::instance(overrideMigrationsPath: $testPath);

    $action = new EnsureMigrationsDirectoriesExists;
    $action->execute();

    expect(is_dir($fullPath))->toBeTrue()
        ->and(is_dir($repeatablePath))->toBeTrue();
});

it('creates nested directory structure recursively', function (): void {
    $testPath = 'tests/deep/nested/path/migrations';
    $fullPath = __DIR__.'/deep/nested/path/migrations';
    $repeatablePath = $fullPath.'/repeatable';

    $cleanupDirs = [
        $repeatablePath,
        $fullPath,
        __DIR__.'/deep/nested/path',
        __DIR__.'/deep/nested',
        __DIR__.'/deep',
    ];

    foreach ($cleanupDirs as $dir) {
        if (is_dir($dir)) {
            rmdir($dir);
        }
    }

    Config::instance(overrideMigrationsPath: $testPath);

    $action = new EnsureMigrationsDirectoriesExists;
    $action->execute();

    expect(is_dir($fullPath))->toBeTrue()
        ->and(is_dir($repeatablePath))->toBeTrue();
});

it('creates directories with correct permissions', function (): void {
    $testPath = 'tests/temp/test-migrations';
    $fullPath = __DIR__.'/temp/test-migrations';
    $repeatablePath = $fullPath.'/repeatable';

    if (is_dir($repeatablePath)) {
        rmdir($repeatablePath);
    }
    if (is_dir($fullPath)) {
        rmdir($fullPath);
    }

    Config::instance(overrideMigrationsPath: $testPath);

    $action = new EnsureMigrationsDirectoriesExists;
    $action->execute();

    expect(is_readable($fullPath))->toBeTrue()
        ->and(is_writable($fullPath))->toBeTrue()
        ->and(is_readable($repeatablePath))->toBeTrue()
        ->and(is_writable($repeatablePath))->toBeTrue();
});
