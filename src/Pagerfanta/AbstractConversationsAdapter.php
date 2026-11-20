<?php

namespace App\Pagerfanta;

use App\Doctrine\DeleteRequestType;
use App\Doctrine\InFolderType;
use App\Doctrine\MessageResultSetMapping;
use App\Entity\Member;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManager;

abstract class AbstractConversationsAdapter
{
    protected Connection $connection;
    protected EntityManager $entityManager;

    /** Words searched for (see setSearch()), null when the list isn't searched */
    private ?string $searchWords = null;

    /** @var list<int> */
    private array $searchSubjectIds = [];

    /** @var list<int> */
    private array $searchMessageIds = [];

    public function __construct(
        EntityManager $entityManager,
        protected Member $member,
        protected int $initiator,
        protected bool $unreadOnly,
    ) {
        $this->connection = $entityManager->getConnection();
        $this->entityManager = $entityManager;
    }

    /**
     * Keeps only the conversations with a message that contains the words (the conversations and the messages
     * without one that MessageSearch found), or with a member whose username or visible name contains them.
     *
     * @param list<int> $subjectIds
     * @param list<int> $messageIds
     */
    public function setSearch(string $words, array $subjectIds, array $messageIds): static
    {
        $words = trim($words);
        $this->searchWords = '' === $words ? null : $words;
        $this->searchSubjectIds = array_map('intval', $subjectIds);
        $this->searchMessageIds = array_map('intval', $messageIds);

        return $this;
    }

    /**
     * Returns the number of results.
     */
    public function getNbResults(): int
    {
        $count = 0;
        try {
            $countQuery = 'SELECT count(*) FROM (' . $this->getConversationsQuery() . ') m'
                . ' WHERE ' . $this->getSearchCondition();
            $result = $this->connection->executeQuery(
                $countQuery,
                $this->getParameters(),
                array_map(static fn ($value) => \is_int($value) ? ParameterType::INTEGER : ParameterType::STRING, $this->getParameters())
            );
            $count = $result->fetchOne();
        } catch (DBALException) {
            // Return 0
        }

        return $count;
    }

    /**
     * Returns a slice of the results.
     */
    public function getSlice(int $offset, int $length): iterable
    {
        $sql = 'SELECT * FROM (' . $this->getConversationsQuery() . ') m';
        $sql .= ' WHERE ' . $this->getSearchCondition();
        $sql .= ' ORDER BY `m`.`created` DESC LIMIT ' . $length . ' OFFSET ' . $offset;

        $query = $this->entityManager->createNativeQuery($sql, new MessageResultSetMapping());
        foreach ($this->getParameters() as $name => $value) {
            $query->setParameter($name, $value);
        }

        $conversations = $query->getResult();

        return $conversations;
    }

    protected function getUnreadCondition(): string
    {
        if ($this->unreadOnly) {
            $unreadCondition = '(m.receiver_id = :memberId) '
                . 'AND (`m`.first_read IS NULL OR `m`.first_read = \'0000-00-00 00:00:00\')';
        } else {
            $unreadCondition = '(m.receiver_id = :memberId OR m.sender_id = :memberId)';
        }

        return $unreadCondition;
    }

    protected function getNotDeletedOrPurgedCondition(): string
    {
        $notDeletedOrPurgedCondition =
            '((m.receiver_id = :memberId) OR (m.sender_id = :memberId)) AND (' .
                '((m.receiver_id = :memberId) AND ' .
                    'm.delete_request NOT LIKE \'%' . DeleteRequestType::RECEIVER_DELETED . '%\'' .
                    ' AND m.delete_request NOT LIKE \'%' . DeleteRequestType::RECEIVER_PURGED . '%\'' .
                ') OR ((m.sender_id = :memberId) AND ' .
                    'm.delete_request NOT LIKE \'%' . DeleteRequestType::SENDER_DELETED . '%\'' .
                    ' AND m.delete_request NOT LIKE \'%' . DeleteRequestType::SENDER_PURGED . '%\'' .
                ')' .
            ')';

        return $notDeletedOrPurgedCondition;
    }

    protected function getInitiatorCondition(): string
    {
        $showStartedByMember = 3 !== $this->initiator;
        $showStartedByOther = 1 !== $this->initiator;
        if (!$showStartedByMember || !$showStartedByOther) {
            $initiatorCondition = '';
            if ($showStartedByMember) {
                $initiatorCondition = '(m.initiator_id = :memberId)';
                if ($showStartedByOther) {
                    $initiatorCondition .= ' AND ';
                }
            }
            if ($showStartedByOther) {
                $initiatorCondition .= '(m.initiator_id <> :memberId)';
            }
        } else {
            $initiatorCondition = '1 = 1';
        }

        return $initiatorCondition;
    }

    protected function getNotSpamCondition(): string
    {
        return '(m.initiator_id = :memberId or folder <> \'' . InFolderType::SPAM . '\')';
    }

    abstract protected function getConversationsQuery(): string;

    /**
     * @return array<string, int|string>
     */
    private function getParameters(): array
    {
        $parameters = ['memberId' => $this->member->getId()];
        if (null !== $this->searchWords) {
            $parameters['searchWords'] = '%' . addcslashes($this->searchWords, '%_\\') . '%';
        }

        return $parameters;
    }

    private function getSearchCondition(): string
    {
        if (null === $this->searchWords) {
            return '1 = 1';
        }

        $conditions = [];
        if ([] !== $this->searchSubjectIds) {
            $conditions[] = 'm.subject_id IN (' . implode(',', $this->searchSubjectIds) . ')';
        }
        if ([] !== $this->searchMessageIds) {
            $conditions[] = 'm.id IN (' . implode(',', $this->searchMessageIds) . ')';
        }
        // The other member, by username, short name or name when it isn't hidden
        $conditions[] = 'EXISTS (SELECT 1 FROM member o'
            . ' WHERE o.id = IF(m.sender_id = :memberId, m.receiver_id, m.sender_id)'
            . ' AND (o.Username LIKE :searchWords OR o.ShortName LIKE :searchWords'
            . ' OR ((o.HideAttribute & ' . Member::NAME_HIDDEN . ') = 0 AND o.Name LIKE :searchWords)))';

        return '(' . implode(' OR ', $conditions) . ')';
    }
}
