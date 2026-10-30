<?php

namespace App\Command;

use App\Doctrine\PostCanStillEditType;
use App\Entity\ForumPost;
use App\Entity\ForumThread;
use App\Entity\Member;
use App\Logger\Logger;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Authors can edit a post until someone replied to the thread and the post is half an hour old. Run it every half
 * hour.
 */
#[AsCommand(
    name: 'forum:lock-edit',
    description: 'Ends editing of posts older than 30 minutes that got a reply',
)]
class ForumLockEditCommand extends Command
{
    public const int MINUTES_TO_EDIT = 30;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Logger $logger,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $locked = $this->entityManager->createQuery(
            'UPDATE ' . ForumPost::class . ' p SET p.ownerCanStillEdit = :disabled
             WHERE p.ownerCanStillEdit = :canEdit
             AND p.created < :deadline
             AND p.thread IS NOT NULL
             AND NOT EXISTS (SELECT 1 FROM ' . ForumThread::class . ' t WHERE t.lastPost = p AND t = p.thread)'
        )
            ->setParameter('disabled', PostCanStillEditType::EDIT_DISABLED)
            ->setParameter('canEdit', PostCanStillEditType::CAN_STILL_EDIT)
            ->setParameter('deadline', new DateTime('-' . self::MINUTES_TO_EDIT . ' minutes'))
            ->execute();

        $message = \sprintf('Ended editing of %d posts that got a reply.', $locked);
        // as the system: bwadmin
        $this->logger->write($message, 'cron_task', $this->entityManager->find(Member::class, 1));
        new SymfonyStyle($input, $output)->success($message);

        return Command::SUCCESS;
    }
}
