<?php

namespace Mig\Commands;

use DateTime;
use Mig\Support\Config;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'create')]
readonly class CreateMigrationCommand
{
    public function __construct()
    {
        //
    }

    public function __invoke(InputInterface $i, OutputInterface $o): int
    {
        $io = new SymfonyStyle($i, $o);
        $o->writeln('📜 Creating a migration');

        $fileName = $this->getFileName($this->askForDescription($io));
        $isRepeatable = $this->askIfRepeatable($io);

        if (strlen($fileName) > 304) {
            $o->writeln('Your migration name is too long. Must be less than 300 characters.');

            return Command::INVALID;
        }

        $filePath = match ($isRepeatable) {
            false => sprintf("%s/%s_%s", Config::migrationsDirectoryPath(), $this->generatePrefix(), $fileName),
            true => sprintf("%s/%s", Config::repeatableMigrationsDirectoryPath(), $fileName),
        };

        touch($filePath);

        $o->writeln("$filePath");
        $o->writeln("✅ created successfully");

        return Command::SUCCESS;
    }

    private function askForDescription(SymfonyStyle $io): string
    {
        return (string) $io->ask('What is the migration description? ie "Create users table"');
    }

    private function getFileName(string $description): string
    {
        $name = str_replace(' ', '_', strtolower($description));

        return $name.'.sql';
    }

    private function generatePrefix(): string
    {
        $today = new DateTime()->format('ymd');
        $seconds = time() - strtotime("today");

        return $today.sprintf('%06d', $seconds);
    }

    private function askIfRepeatable(SymfonyStyle $io): bool
    {
        return $io->confirm('Is this migration repeatable? Can it be run multiple times?', false);
    }
}
