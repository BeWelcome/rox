<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

class CreateTestDatabase extends Command
{
    /**
     * @var string
     *
     * the name of the command (the part after "bin/console")
     */
    protected static $defaultName = 'test:database:create';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure()
    {
        $descriptionAndHelp = 'Creates a database and seeds it so that it can be used for local development';
        $this
            ->setDescription($descriptionAndHelp)
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
            ->setHelp($descriptionAndHelp)
        ;
    }

    /**
     * @return int
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * The method is 123 lines long due to the way the command output is organized.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $nullOutput = new NullOutput();
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
            $command = $this->getApplication()->find('doctrine:database:drop');

            $arguments = [
                '--force' => true,
            ];

            $dropDatabase = new ArrayInput($arguments);

            // Ignore return code and output. If drop fails either no database existed yet or MYSQL isn't ready
            $returnCode = $command->run($dropDatabase, $nullOutput);
        }

        $output->writeln([
            'Creating the database',
            '',
        ]);
        $command = $this->getApplication()->find('doctrine:database:create');

        $createDatabase = new ArrayInput(['--if-not-exists' => true]);

        $returnCode = $command->run($createDatabase, $output);
        if ($returnCode) {
            $output->writeln([
                'Failed creating the database (see output above for reasons).',
                '',
            ]);

            return 1;
        }

        $output->writeln([
            'Creating the schema',
            '',
        ]);
        $command = $this->getApplication()->find('doctrine:schema:create');

        $createSchema = new ArrayInput([]);

        $returnCode = $command->run($createSchema, $output);
        if ($returnCode) {
            $output->writeln([
                'Failed creating the schema (see output above for reasons).',
                '',
            ]);

            return 1;
        }

        $output->writeln([
            'Adding stored functions',
            '',
        ]);
        $command = $this->getApplication()->find('doctrine:migrations:migrate');

        $addFunctions = new ArrayInput([]);
        $addFunctions->setInteractive(false);

        $returnCode = $command->run($addFunctions, $output);
        if ($returnCode) {
            $output->writeln([
                'Failed adding functions (see above for reasons).',
                '',
            ]);

            return 1;
        }

        $output->writeln([
            'Seeding the database',
            '',
        ]);
        $command = $this->getApplication()->find('hautelook:fixtures:load');

        $addMissingTranslations = new ArrayInput([]);
        $addMissingTranslations->setInteractive(false);

        $returnCode = $command->run($addMissingTranslations, $output);
        if ($returnCode) {
            $output->writeln([
                'Failed seeding the database.',
                '',
            ]);

            return 1;
        }

        // Now set id for English to 0 as the old code expects that
        $connection = $this->entityManager->getConnection();
        $connection->executeStatement("
            SET FOREIGN_KEY_CHECKS=0;
            UPDATE languages SET id = 0 WHERE ShortCode = 'en';
            UPDATE words SET IdLanguage = 0 WHERE ShortCode = 'en';
            UPDATE memberslanguageslevel SET IdLanguage = 0 WHERE IdLanguage = 1;
            UPDATE languages SET id = 1 WHERE ShortCode = 'fr';
            UPDATE words SET IdLanguage = 1 WHERE ShortCode = 'fr';
            UPDATE memberslanguageslevel SET IdLanguage = 1 WHERE IdLanguage = 2;
            SET FOREIGN_KEY_CHECKS=1;
        ");

        $output->writeln([
            'Importing missing translations',
            '',
        ]);
        $command = $this->getApplication()->find('translations:add:missing');

        $addMissingTranslations = new ArrayInput([]);
        $addMissingTranslations->setInteractive(false);

        $returnCode = $command->run($addMissingTranslations, $output);
        if ($returnCode) {
            $output->writeln([
                'Failed seeding the database.',
                '',
            ]);

            return 1;
        }

        $output->writeln([
            '',
            'Finished have fun.',
        ]);

        return 0;
    }
}
