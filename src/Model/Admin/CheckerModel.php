<?php

namespace App\Model\Admin;

use App\Doctrine\InFolderType;
use App\Doctrine\MessageStatusType;
use App\Doctrine\SpamInfoType;
use App\Entity\Message;
use App\Repository\MessageRepository;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Pagerfanta;

class CheckerModel
{
    private EntityManagerInterface $entityManager;
    private Mailer $mailer;

    public function __construct(EntityManagerInterface $entityManager, Mailer $mailer)
    {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
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
                if (str_contains($message->getSpamInfo(), SpamInfoType::SPAM_BLOCKED_WORD)) {
                    $message->setFolder(InFolderType::NORMAL);
                    $this->sendNotification($message);
                }
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

    public function getBlockWordsMessages(int $page = 1, int $limit = 10): Pagerfanta
    {
        /** @var MessageRepository $repository */
        $repository = $this->entityManager->getRepository(Message::class);

        return $repository->findBlockWordsMessages($page, $limit);
    }

    public function getProcessedBlockWordsMessages(int $page = 1, int $limit = 10): Pagerfanta
    {
        /** @var MessageRepository $repository */
        $repository = $this->entityManager->getRepository(Message::class);

        return $repository->findProcessedBlockWordsMessages($page, $limit);
    }

    private function sendNotification(Message $message): void
    {
        // Is this a message or a request?
        if (null === $message->getRequest()) {
            $this->mailer->sendMessageNotificationEmail(
                $message->getSender(),
                $message->getReceiver(),
                'message',
                [
                    'message' => $message,
                    'subject' => $message->getSubject()->getSubject(),
                    'body' => $message->getMessage(),
                ]
            );
        } else {
            $this->mailer->sendMessageNotificationEmail($message->getSender(), $message->getReceiver(), 'request', [
                'host' => $message->getReceiver(),
                'subject' => $message->getSubject()->getSubject(),
                'message' => $message,
                'request' => $message->getRequest(),
                'changed' => false,
            ]);
        }
    }
}
