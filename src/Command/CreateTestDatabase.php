<?php

namespace App\Command;

use App\Model\StatisticsModel;
use DateInterval;
use DatePeriod;
use DateTime;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Validator\Date;

class CreateTestDatabase extends Command
{
    /**
     * @var string
     *
     * the name of the command (the part after "bin/console")
     */
    protected static $defaultName = 'test:database:create';

    /** EntityManager $entityManager */
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
                'Will create a new database even if one already exist')
            ->addOption(
                'drop',
                null,
                InputOption::VALUE_NONE,
                'Will drop the database if one already exist. Needs to be used with --force.')
            ->setHelp($descriptionAndHelp)
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
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
                '--force'  => true,
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

        $createDatabase = new ArrayInput([]);

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
            'Seeding the database',
            '',
        ]);
        $command = $this->getApplication()->find('hautelook:fixtures:load');

        $loadFixtures = new ArrayInput([
            '--no-interaction' => true,
            '-n' => true,
        ]);

        $returnCode = $command->run($loadFixtures, $output);
        if ($returnCode) {
            $output->writeln([
                'Failed seeding the database.',
                '',
            ]);
            return 1;
        }

        $output->writeln([
            'Fixing things :)',
            '',
        ]);
        // Now set id for English to 0 as the old code expects that
        $connection = $this->entityManager->getConnection();
        $connection->executeUpdate("
            SET FOREIGN_KEY_CHECKS=0;
            UPDATE languages SET id = 0 WHERE ShortCode = 'en';
            UPDATE words SET IdLanguage = 0 WHERE ShortCode = 'en';
            UPDATE languages SET id = 1 WHERE ShortCode = 'fr';
            UPDATE words SET IdLanguage = 1 WHERE ShortCode = 'fr';
            SET FOREIGN_KEY_CHECKS=1;
        ");

        $output->writeln([
            '',
            'Finished have fun.',
        ]);

        return 0;
    }
}
