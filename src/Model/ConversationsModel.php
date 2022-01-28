<?php

namespace App\Model;

use App\Doctrine\DeleteRequestType;
use App\Doctrine\InFolderType;
use App\Doctrine\MessageStatusType;
use App\Doctrine\SpamInfoType;
use App\Entity\HostingRequest;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use App\Repository\MessageRepository;
use App\Service\Mailer;
use App\Utilities\ConversationThread;
use DateTime;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Exception;
use InvalidArgumentException;
use Pagerfanta\Pagerfanta;

class ConversationsModel
{
    private EntityManagerInterface $entityManager;
    private ConversationModel $conversationModel;
    private ConversationThread $conversationThread;

    public function __construct(ConversationModel $conversationModel, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->conversationModel = $conversationModel;
        $this->conversationThread = new ConversationThread($this->entityManager);
    }

    /**
     * Mark a message as purged (can not be unmarked).
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function markConversationsPurged(Member $member, array $conversationIds): void
    {
        $threads = $this->getThreadsForConversationIds($conversationIds);

        foreach ($threads as $thread) {
            $this->conversationModel->markConversationPurged($member, $thread);
        }
    }

    public function markConversationsDeleted(Member $member, array $conversationIds): void
    {
        $threads = $this->getThreadsForConversationIds($conversationIds);

        foreach ($threads as $thread) {
            $this->conversationModel->markConversationDeleted($member, $thread);
        }
    }

    public function unmarkConversationsDeleted(Member $member, array $conversationIds): void
    {
        $threads = $this->getThreadsForConversationIds($conversationIds);

        foreach ($threads as $thread) {
            $this->conversationModel->unmarkConversationDeleted($member, $thread);
        }
    }

    public function markConversationsAsSpam(Member $member, array $conversationIds): void
    {
        $threads = $this->getThreadsForConversationIds($conversationIds);

        foreach ($threads as $thread) {
            $this->conversationModel->markConversationAsSpam($member, $thread);
        }
    }

    public function unmarkConversationsAsSpam(Member $member, array $conversationIds): void
    {
        $threads = $this->getThreadsForConversationIds($conversationIds);

        foreach ($threads as $thread) {
            $this->conversationModel->unmarkConversationAsSpam($member, $thread);
        }
    }

    public function getConversationsWith(
        Member $member,
        Member $other,
        string $sort,
        string $sortDir,
        int $page = 1,
        int $limit = 10
    ): Pagerfanta {
        /** @var MessageRepository $repository */
        $repository = $this->entityManager->getRepository(Message::class);

        return $repository->findAllMessagesBetween($member, $other, $sort, $sortDir, $page, $limit);
    }

    private function getThreadsForConversationIds(array $conversationIds): array
    {
        $messageRepository = $this->entityManager->getRepository(Message::class);
        $conversations = $messageRepository->findBy(['id' => $conversationIds]);

        $threads = [];
        foreach ($conversations as $conversation) {
            $threads[] = $this->conversationThread->getThread($conversation);
        }

        return $threads;
    }
}
