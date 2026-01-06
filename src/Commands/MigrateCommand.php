<?php

namespace Mig\Commands;

use Mig\Actions\RetrievePendingMigrations;
use Mig\Actions\RetrievePendingRepeatableMigrations;
use Mig\Actions\RunMigration;
use Mig\Actions\StoreMigrationExecuted;
use Mig\Actions\StoreRepeatableMigrationExecuted;
use Mig\Config;
use Mig\Database;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'migrate')]
class MigrateCommand
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::instance();
    }

    public function __invoke(InputInterface $i, OutputInterface $o): int
    {
        $this->runMigrations($o);
        $this->runRepeatableMigrations($o);

        $o->writeln('🥱 Done, no more migrations to run.');

        return Command::SUCCESS;
    }

    private function runMigrations(OutputInterface $o): void
    {
        $storeMigration = new StoreMigrationExecuted;
        $pendingMigrations = new RetrievePendingMigrations($this->db)->execute();

        if (count($pendingMigrations) === 0) {
            $o->writeln('☑️ No pending migrations...');
        }

        foreach ($pendingMigrations as $migrationName) {
            $o->writeln("Running migration $migrationName");

            $migrationPath = sprintf('%s/%s', Config::instance()->migrationsDirPath, $migrationName);

            [$success, $info] = new RunMigration()->execute($migrationPath);

            if ($success) {
                $storeMigration->execute($migrationName);
            }

            $o->writeln($info);
        }
    }

    private function runRepeatableMigrations(OutputInterface $o): void
    {
        $storeMigration = new StoreRepeatableMigrationExecuted;
        $pendingMigrations = new RetrievePendingRepeatableMigrations($this->db)->execute();

        if (count($pendingMigrations) === 0) {
            $o->writeln('☑️ No pending repeatable migrations...');

        }

        foreach ($pendingMigrations as $migrationName) {

            $o->writeln("Running repeatable migration $migrationName");

            $migrationPath = sprintf('%s/%s', Config::instance()->repeatableMigrationsDirPath, $migrationName);

            [$success, $info] = new RunMigration()->execute($migrationPath);

            if ($success) {
                $storeMigration->execute($migrationName);
            }

            $o->writeln($info);
        }
    }
}
