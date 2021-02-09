<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AddInitiatorCommand extends Command
{
    protected static $defaultName = 'database:add:initiator';

    private array $initiators = [];
    private array $parents = [];

    private EntityManagerInterface $entityManager;

    /**
     * TestAddinitiatorCommand constructor.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Set the initiator column for all messages')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '16G');

        $io = new SymfonyStyle($input, $output);
        $io->block('Adding initiator values for conversations');

        $connection = $this->entityManager->getConnection();

        $count = $connection->fetchOne('SELECT count(*) from messages');

        $progress = new ProgressBar($output, $count);
        $progress->start();

        $statement = $connection->executeQuery('SELECT * FROM messages');

        for ($i = 0; $i < $count; ++$i) {
            $progress->advance();
            $row = $statement->fetch();

            $messageId = $row['id'];
            if (30180 === $messageId) {
                continue;
            }
            if ('0' === $row['IdParent'] || null === $row['IdParent']) {
                $this->initiators[$messageId] = $row['IdSender'];
                $initiator = $row['IdSender'];
            } else {
                $this->parents[$messageId] = $row['IdParent'];
                $initiator = $this->getinitiator($messageId);
            }

            $connection->executeQuery('UPDATE messages m SET initiator_id = ' . $initiator . ' WHERE m.id = ' . $messageId);
        }
        $progress->finish();

        return Command::SUCCESS;
    }

    private function getinitiator($parent)
    {
        while (isset($this->parents[$parent])) {
            $parent = $this->parents[$parent];
        }

        $initiator = 0;
        if (isset($this->initiators[$parent])) {
            $initiator = $this->initiators[$parent];
        }

        return $initiator;
    }
}
