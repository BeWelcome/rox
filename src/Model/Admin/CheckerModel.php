<?php

namespace App\Model\Admin;

use App\Doctrine\MessageStatusType;
use App\Doctrine\SpamInfoType;
use App\Entity\Message;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Pagerfanta;

class CheckerModel
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function markAsSpamByChecker(array $messageIds): void
    {
        /** @var MessageRepository $repository */
        $repository = $this->entityManager->getRepository(Message::class);

        $messages = $repository->findBy([
            'id' => $messageIds,
        ]);

        /** @var Message $message */
        foreach ($messages as $message) {
            $message
                ->setStatus(MessageStatusType::CHECKED)
                ->addToSpamInfo(SpamInfoType::CHECKER_SAYS_SPAM);
            $this->entityManager->persist($message);
        }
        $this->entityManager->flush();
    }

    public function unmarkAsSpamByChecker(array $messageIds): void
    {
        /** @var MessageRepository $repository */
        $repository = $this->entityManager->getRepository(Message::class);

        $messages = $repository->findBy([
            'id' => $messageIds,
        ]);

        /** @var Message $message */
        foreach ($messages as $message) {
            // \todo If message wasn't sent yet, send it now
            if (MessageStatusType::FROZEN === $message->getStatus()) {
                $message->setStatus(MessageStatusType::SEND);
            } else {
                $message->setStatus(MessageStatusType::CHECKED);
            }
            $this->entityManager->persist($message);
        }
        $this->entityManager->flush();
    }

    public function getReportedMessages(int $page = 1, int $limit = 10): Pagerfanta
    {
        /** @var MessageRepository $repository */
        $repository = $this->entityManager->getRepository(Message::class);

        return $repository->findReportedMessages($page, $limit);
    }

    public function getProcessedReportedMessages(int $page = 1, int $limit = 10): Pagerfanta
    {
        /** @var MessageRepository $repository */
        $repository = $this->entityManager->getRepository(Message::class);

        return $repository->findProcessedReportedMessages($page, $limit);
    }
}
