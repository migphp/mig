<?php

namespace Mig\Commands;

use Mig\Actions\EnsureMigrationsTablesExists;
use Mig\Actions\RetrievePendingMigrations;
use Mig\Actions\RunMigration;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'migrate')]
class MigrateCommand
{
    public function __invoke(InputInterface $i, OutputInterface $o): int
    {
        new EnsureMigrationsTablesExists()();
        $this->runMigrations($o);
        $this->runRepeatableMigrations();
    }

    private function runMigrations(OutputInterface $o): void
    {
        $pendingMigrations = new RetrievePendingMigrations()();

        foreach ($pendingMigrations as $migration) {
            $o->writeln("Running migration: $migration");
            $result = new RunMigration($migration)();

            $o->writeln($result);
        }
    }

    private function runRepeatableMigrations(): void
    {
    }
}
