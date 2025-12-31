<?php

namespace Mig\Commands;

use DateTime;
use Mig\Support\ProjectPath;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'create')]
readonly class CreateMigrationCommand
{
    private string $dirPath;

    public function __construct(
        string $location
    ) {
        $this->dirPath = ProjectPath::get().'/'.$location;
        $this->ensureDirPathExists();
    }

    public function __invoke(InputInterface $i, OutputInterface $o): int
    {
        $io = new SymfonyStyle($i, $o);
        $o->writeln('📜 Creating a migration');

        $fileName = $this->getFileName($this->askForDescription($io));

        $filePath = "{$this->dirPath}/{$fileName}";

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

        return $this->generatePrefix().'_'.$name.'.sql';
    }


    private function generatePrefix(): string
    {
        $today = new DateTime()->format('ymd');
        $seconds = time() - strtotime("today");

        return $today.sprintf('%06d', $seconds);
    }

    private function ensureDirPathExists(): void
    {
        if (!is_dir($this->dirPath)) {
            mkdir($this->dirPath, 0777, true);
        }
    }
}
