<?php

declare(strict_types=1);

use Mig\Actions\EnsureMigrationsTablesExists;
use Mig\Config;
use Mig\Database;
use Mig\ValueObjects\DatabaseConfig;

beforeEach(function (): void {
    Config::reset();
    $db = Database::instance();
    $db->pdo->exec('drop table if exists mig_migrations');
    $db->pdo->exec('drop table if exists mig_repeatable_migrations');
});

afterEach(function (): void {
    $db = Database::instance();
    $db->pdo->exec('drop table if exists mig_migrations');
    $db->pdo->exec('drop table if exists mig_repeatable_migrations');
});

it('creates mig_migrations table when it does not exist', function (): void {
    $action = new EnsureMigrationsTablesExists;
    $action->execute();

    $db = Database::instance();

    $result = $db->pdo->query("select exists (
            select from information_schema.tables
            where table_name = 'mig_migrations' )"
    )->fetchColumn();

    expect($result)->toBeTrue();
});

it('creates mig_repeatable_migrations table when it does not exist', function (): void {
    $action = new EnsureMigrationsTablesExists;
    $action->execute();

    $db = Database::instance();

    $result = $db->pdo->query("select exists (
            select from information_schema.tables
            where table_name = 'mig_repeatable_migrations' )"
    )->fetchColumn();

    expect($result)->toBeTrue();
});

it('creates both tables in single execution', function (): void {
    $action = new EnsureMigrationsTablesExists;
    $action->execute();

    $db = Database::instance();

    $migrations = $db->pdo->query("select exists (
            select from information_schema.tables
            where table_name = 'mig_migrations' )"
    )->fetchColumn();

    $repeatableMigrations = $db->pdo->query("select exists (
            select from information_schema.tables
            where table_name = 'mig_repeatable_migrations' )"
    )->fetchColumn();

    expect($migrations)->toBeTrue()
        ->and($repeatableMigrations)->toBeTrue();
});

it('does not error when tables already exist', function (): void {
    $action = new EnsureMigrationsTablesExists;

    $action->execute();
    $action->execute();

    $db = Database::instance();

    $migrations = $db->pdo->query("select exists (
            select from information_schema.tables
            where table_name = 'mig_migrations' )"
    )->fetchColumn();

    $repeatableMigrations = $db->pdo->query("select exists (
            select from information_schema.tables
            where table_name = 'mig_repeatable_migrations' )"
    )->fetchColumn();

    expect($migrations)->toBeTrue()
        ->and($repeatableMigrations)->toBeTrue();
});

it('creates mig_migrations table with correct schema', function (): void {
    $action = new EnsureMigrationsTablesExists;
    $action->execute();

    $db = Database::instance();

    $columns = $db->pdo->query(
        "select column_name, data_type, character_maximum_length, is_nullable, column_default
        from information_schema.columns
        where table_name = 'mig_migrations'
        order by ordinal_position"
    )->fetchAll();

    expect($columns)->toHaveCount(3)
        ->and($columns[0]['column_name'])->toBe('id')
        ->and($columns[0]['data_type'])->toBe('bigint')
        ->and($columns[1]['column_name'])->toBe('migration')
        ->and($columns[1]['data_type'])->toBe('character varying')
        ->and($columns[1]['character_maximum_length'])->toBe(317)
        ->and($columns[2]['column_name'])->toBe('executed_at')
        ->and($columns[2]['data_type'])->toBe('timestamp with time zone');
});

it('creates mig_repeatable_migrations table with correct schema', function (): void {
    $action = new EnsureMigrationsTablesExists;
    $action->execute();

    $db = Database::instance();

    $columns = $db->pdo->query(
        "select column_name, data_type, character_maximum_length, is_nullable
        from information_schema.columns
        where table_name = 'mig_repeatable_migrations'
        order by ordinal_position"
    )->fetchAll();

    expect($columns)->toHaveCount(4)
        ->and($columns[0]['column_name'])->toBe('id')
        ->and($columns[0]['data_type'])->toBe('bigint')
        ->and($columns[1]['column_name'])->toBe('migration')
        ->and($columns[1]['data_type'])->toBe('character varying')
        ->and($columns[1]['character_maximum_length'])->toBe(304)
        ->and($columns[2]['column_name'])->toBe('checksum')
        ->and($columns[2]['data_type'])->toBe('character varying')
        ->and($columns[2]['character_maximum_length'])->toBe(64)
        ->and($columns[3]['column_name'])->toBe('executed_at')
        ->and($columns[3]['data_type'])->toBe('timestamp with time zone');
});

it('creates mig_migrations table with primary key on id', function (): void {
    $action = new EnsureMigrationsTablesExists;
    $action->execute();

    $db = Database::instance();

    $hasPrimaryKey = $db->pdo->query(
        "select exists (
            select from information_schema.table_constraints
            where table_name = 'mig_migrations'
            and constraint_type = 'PRIMARY KEY'
        )"
    )->fetchColumn();

    expect($hasPrimaryKey)->toBeTrue();
});

it('creates mig_repeatable_migrations table with primary key on id', function (): void {
    $action = new EnsureMigrationsTablesExists;
    $action->execute();

    $db = Database::instance();

    $hasPrimaryKey = $db->pdo->query(
        "select exists (
            select from information_schema.table_constraints
            where table_name = 'mig_repeatable_migrations'
            and constraint_type = 'PRIMARY KEY'
        )"
    )->fetchColumn();

    expect($hasPrimaryKey)->toBeTrue();
});

it('creates mig_repeatable_migrations table with unique constraint on migration', function (): void {
    $action = new EnsureMigrationsTablesExists;
    $action->execute();

    $db = Database::instance();

    $hasUniqueConstraint = $db->pdo->query(
        "select exists (
            select from information_schema.table_constraints
            where table_name = 'mig_repeatable_migrations'
            and constraint_type = 'UNIQUE'
        )"
    )->fetchColumn();

    expect($hasUniqueConstraint)->toBeTrue();
});

it('allows inserting data into mig_migrations table', function (): void {
    $action = new EnsureMigrationsTablesExists;
    $action->execute();

    $db = Database::instance();

    $statement = $db->pdo->prepare(
        'insert into mig_migrations (migration) values (?)'
    );
    $statement->execute(['001_test_migration.sql']);

    $count = $db->pdo->query('select count(*) from mig_migrations')->fetchColumn();

    expect($count)->toBe(1);
});

it('allows inserting data into mig_repeatable_migrations table', function (): void {
    $action = new EnsureMigrationsTablesExists;
    $action->execute();

    $db = Database::instance();

    $statement = $db->pdo->prepare(
        'insert into mig_repeatable_migrations (migration, checksum) values (?, ?)'
    );
    $statement->execute(['R__test_migration.sql', 'abc123']);

    $count = $db->pdo->query('select count(*) from mig_repeatable_migrations')->fetchColumn();

    expect($count)->toBe(1);
});

it('auto-generates id for mig_migrations table', function (): void {
    $action = new EnsureMigrationsTablesExists;
    $action->execute();

    $db = Database::instance();

    $statement = $db->pdo->prepare(
        'insert into mig_migrations (migration) values (?) returning id'
    );
    $statement->execute(['001_test.sql']);
    $id = $statement->fetchColumn();

    expect($id)->toBeGreaterThan(0);
});

it('auto-generates id for mig_repeatable_migrations table', function (): void {
    $action = new EnsureMigrationsTablesExists;
    $action->execute();

    $db = Database::instance();

    $statement = $db->pdo->prepare(
        'insert into mig_repeatable_migrations (migration, checksum) values (?, ?) returning id'
    );
    $statement->execute(['R__test.sql', 'checksum']);
    $id = $statement->fetchColumn();

    expect($id)->toBeGreaterThan(0);
});
