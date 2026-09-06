<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ForumCleanupCommand extends Command
{
    protected static $defaultName = 'forum:cleanup';

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Disallow editing after 30 minutes if someone replied')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $connection = $this->entityManager->getConnection();

        $affectedRows = $connection->executeStatement("
            UPDATE forums_posts , forums_threads SET OwnerCanStillEdit='No'
            WHERE forums_threads.id = forums_posts.threadid
            AND last_postid != forums_posts.id
            AND OwnerCanStillEdit = 'Yes' and forums_posts.create_time<date_sub(now(),interval 30 minute);
        ");

        $connection->executeStatement("
            INSERT INTO logs(IdMember,Str,Type,created) values(1,'Disabling Edit for forums post older than 30 minutes which have been replied: {$affectedRows}.','cron_task',now()) ;

        ");

        return Command::SUCCESS;
    }
}
