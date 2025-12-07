<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'test:database:create',
    description: 'Creates a database and seeds it so that it can be used for local development',
    aliases: [],
    hidden: false,
)]
class CreateTestDatabase extends Command
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'force',
                null,
                InputOption::VALUE_NONE,
                'Will create a new database even if one already exist'
            )
            ->addOption(
                'drop',
                null,
                InputOption::VALUE_NONE,
                'Will drop the database if one already exist. Needs to be used with --force.'
            )
            ->addOption(
                'translations',
                null,
                InputOption::VALUE_NONE,
                'Will download the current translations and languages.'
            )
        ;
    }

    /**
     * @SuppressWarnings("PHPMD.ExcessiveMethodLength")
     *
     * \todo think about a better way to call the external commands one after another.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $phpBinaryFinder = new PhpExecutableFinder();
        $phpBinaryPath = $phpBinaryFinder->find();

        $output->writeln([
            'Creating test database',
            '======================',
            '',
        ]);

        $drop = $input->getOption('drop');
        $force = $input->getOption('force');

        if ($drop && $force) {
            $output->writeln([
                'Dropping the database',
                '',
            ]);

            $process = new Process([$phpBinaryPath, 'bin/console', 'doctrine:database:drop', '--force', '--no-interaction']);
            $process->run();
            if (!$process->isSuccessful()) {
                $output->writeln([
                    'Dropping the database failed (continuing anyway).',
                    '',
                ]);
            }
        }

        $output->writeln([
            'Creating the database',
            '',
        ]);
        $process = new Process([$phpBinaryPath, 'bin/console', 'doctrine:database:create', '--if-not-exists', '--no-interaction']);
        $process->run();

        if (!$process->isSuccessful()) {
            $output->writeln([
                'Failed creating the database (see output below for reasons).',
                '',
                $process->getOutput(),
                $process->getErrorOutput(),
            ]);

            return Command::FAILURE;
        }

        $output->writeln([
            'Creating the schema',
            '',
        ]);

        $process = new Process([$phpBinaryPath, 'bin/console', 'doctrine:schema:create', '--no-interaction']);
        $process->run();

        if (!$process->isSuccessful()) {
            $output->writeln([
                'Failed creating the schema (see output below for reasons).',
                '',
                $process->getOutput(),
                $process->getErrorOutput(),
            ]);

            return Command::FAILURE;
        }

        $output->writeln([
            'Adding stored functions',
            '',
        ]);

        $process = new Process([$phpBinaryPath, 'bin/console', 'doctrine:migrations:migrate', '--no-interaction']);
        $process->run();

        if (!$process->isSuccessful()) {
            $output->writeln([
                'Failed adding functions (see below for reasons).',
                '',
                $process->getOutput(),
                $process->getErrorOutput(),
            ]);

            return Command::FAILURE;
        }

        $output->writeln([
            'Seeding the database',
            '',
        ]);

        $process = new Process([$phpBinaryPath, 'bin/console', 'hautelook:fixtures:load', '--no-interaction']);
        $process->run();

        if (!$process->isSuccessful()) {
            $output->writeln([
                'Failed seeding the database.',
                '',
                $process->getOutput(),
                $process->getErrorOutput(),
            ]);

            return Command::FAILURE;
        }

        $output->writeln([
            'Fixing ids in the database',
            '',
        ]);

        // Now set id for English to 0 as the old code expects that
        $connection = $this->entityManager->getConnection();
        $connection->executeQuery("
            SET FOREIGN_KEY_CHECKS=0;
            UPDATE languages SET id = 0 WHERE ShortCode = 'en';
            UPDATE words SET IdLanguage = 0 WHERE ShortCode = 'en';
            UPDATE member_language_level SET language_id = 0 WHERE language_id = 1;
            UPDATE languages SET id = 1 WHERE ShortCode = 'fr';
            UPDATE words SET IdLanguage = 1 WHERE ShortCode = 'fr';
            UPDATE member_language_level SET language_id = 1 WHERE language_id = 2;
            SET FOREIGN_KEY_CHECKS=1;
        ");

        $output->writeln([
            '',
            'Finished have fun.',
        ]);

        return Command::SUCCESS;
    }
}
