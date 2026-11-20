<?php

namespace App\Service;

use App\Doctrine\DeleteRequestType;
use App\Doctrine\InFolderType;
use App\Entity\Member;
use App\Entity\Message;
use App\Pagerfanta\AbstractConversationsAdapter;
use App\Pagerfanta\ConversationsAdapter;
use App\Pagerfanta\ConversationsWithAdapter;
use App\Pagerfanta\DeletedAdapter;
use App\Pagerfanta\InvitationsAdapter;
use App\Pagerfanta\MessagesAdapter;
use App\Pagerfanta\RequestsAdapter;
use App\Pagerfanta\SpamAdapter;
use App\Utilities\ItemsPerPageTraits;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Twig\Attribute\AsTwigFunction;

/**
 * The folder list shown next to an open conversation (view and reply pages).
 */
class InboxFolders
{
    use ItemsPerPageTraits;

    private const array FOLDERS = ['conversations', 'messages', 'requests', 'invitations', 'spam', 'deleted'];

    /** The words of the list's search, and the newest matching message of each conversation (MessageSearch::find()) */
    private string $searchWords = '';

    /** @var array<string, array{id: int, count: int}> */
    private array $searchHits = [];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
        private readonly MessageSearch $messageSearch,
    ) {
    }

    /**
     * The folder the conversation was opened from (?folder=…), so that the list next to it stays the same.
     */
    public function getFolder(Request $request, string $default = 'conversations'): string
    {
        $folder = $request->query->get('folder', '');

        return \in_array($folder, self::FOLDERS, true) ? $folder : $default;
    }

    /**
     * The list filters (?initiator=1|3, ?unread_only=1, ?q=words), as on the folder pages.
     *
     * @return array{initiator: int, unreadOnly: bool, search: string}
     */
    public function getFilters(Request $request): array
    {
        $initiator = (int) $request->query->get('initiator', '2');

        return [
            'initiator' => \in_array($initiator, [1, 2, 3], true) ? $initiator : 2,
            'unreadOnly' => '1' === $request->query->get('unread_only', '0'),
            'search' => mb_substr(trim((string) $request->query->get('q', '')), 0, 100),
        ];
    }

    /**
     * Keeps only the conversations of the list that match the words (see AbstractConversationsAdapter::setSearch()).
     */
    public function search(AbstractConversationsAdapter $adapter, Member $member, string $words): AbstractConversationsAdapter
    {
        if ('' === trim($words)) {
            return $adapter;
        }

        $found = $this->messageSearch->find($member, $words);
        $this->searchWords = $words;
        $this->searchHits = $found['hits'];

        return $adapter->setSearch($words, $found['subjectIds'], $found['messageIds']);
    }

    /**
     * For a conversation of the searched list: the part of its newest message with the words, "…" around, and how
     * many of its messages have them (Twig: inbox_search_snippet(message)); null when the list isn't searched or
     * the words aren't in a message (only in the subject or the name).
     *
     * @return array{text: string, count: int}|null
     */
    #[AsTwigFunction('inbox_search_snippet')]
    public function getSearchSnippet(Message $message): ?array
    {
        if ('' === $this->searchWords) {
            return null;
        }
        $key = null === $message->getSubject() ? 'm' . $message->getId() : 's' . $message->getSubject()->getId();
        $hit = $this->searchHits[$key] ?? null;
        $found = null === $hit ? null : $this->entityManager->find(Message::class, $hit['id']);
        $text = null === $found ? null : MessageSearch::snippet($found->getMessage(), $this->searchWords);

        return null === $text ? null : ['text' => $text, 'count' => $hit['count']];
    }

    public function getConversations(Member $member, string $folder, int $initiator = 2, bool $unreadOnly = false, string $search = ''): Pagerfanta
    {
        $adapter = match ($folder) {
            'messages' => MessagesAdapter::class,
            'requests' => RequestsAdapter::class,
            'invitations' => InvitationsAdapter::class,
            'spam' => SpamAdapter::class,
            'deleted' => DeletedAdapter::class,
            default => ConversationsAdapter::class,
        };

        $conversations = new Pagerfanta($this->search(new $adapter($this->entityManager, $member, $initiator, $unreadOnly), $member, $search));
        $conversations->setMaxPerPage($this->getItemsPerPage($member));
        $conversations->setCurrentPage(1);

        return $conversations;
    }

    /**
     * Unread conversations per folder (all, messages, requests, invitations) of the
     * logged-in member, for the folder menu (Twig: inbox_unread_counts()).
     *
     * @return array<string, int>
     */
    #[AsTwigFunction('inbox_unread_counts')]
    public function getUnreadCounts(): array
    {
        $member = $this->security->getUser();
        if (!$member instanceof Member) {
            return [];
        }

        return [
            'conversations' => new ConversationsAdapter($this->entityManager, $member, 2, true)->getNbResults(),
            'messages' => new MessagesAdapter($this->entityManager, $member, 2, true)->getNbResults(),
            'requests' => new RequestsAdapter($this->entityManager, $member, 2, true)->getNbResults(),
            'invitations' => new InvitationsAdapter($this->entityManager, $member, 2, true)->getNbResults(),
        ];
    }

    /**
     * All the conversations in the inbox of the logged-in member, read or not, for the
     * "All conversations" link of the home page (Twig: inbox_total_count()).
     */
    #[AsTwigFunction('inbox_total_count')]
    public function getTotalCount(): int
    {
        $member = $this->security->getUser();
        if (!$member instanceof Member) {
            return 0;
        }

        return new ConversationsAdapter($this->entityManager, $member, 2, false)->getNbResults();
    }

    /**
     * The latest message between the logged-in member and $other that is still in the inbox
     * (a plain conversation, a hosting request or an invitation), or null when there is none
     * (Twig: conversation_with(member), for the status strip of a profile). With $messagesOnly,
     * only a plain conversation (conversation_with(member, true), for the "Send" button: Send
     * writes a message, it doesn't open the other member's hosting request).
     */
    #[AsTwigFunction('conversation_with')]
    public function getConversationWith(Member $other, bool $messagesOnly = false): ?Message
    {
        $member = $this->security->getUser();
        if (!$member instanceof Member || $member === $other) {
            return null;
        }

        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('m')
            ->from(Message::class, 'm')
            ->where('(m.sender = :member AND m.receiver = :other AND m.deleteRequest NOT LIKE :senderDeleted AND m.deleteRequest NOT LIKE :senderPurged)'
                . ' OR (m.receiver = :member AND m.sender = :other AND m.deleteRequest NOT LIKE :receiverDeleted AND m.deleteRequest NOT LIKE :receiverPurged)')
            ->andWhere('m.initiator = :member OR m.folder <> :spam')
            ->setParameter('member', $member)
            ->setParameter('other', $other)
            ->setParameter('senderDeleted', '%' . DeleteRequestType::SENDER_DELETED . '%')
            ->setParameter('senderPurged', '%' . DeleteRequestType::SENDER_PURGED . '%')
            ->setParameter('receiverDeleted', '%' . DeleteRequestType::RECEIVER_DELETED . '%')
            ->setParameter('receiverPurged', '%' . DeleteRequestType::RECEIVER_PURGED . '%')
            ->setParameter('spam', InFolderType::SPAM)
            ->orderBy('m.id', 'DESC')
            ->setMaxResults(1);
        if ($messagesOnly) {
            $queryBuilder->andWhere('m.request IS NULL');
        }

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * How many conversations the logged-in member has with $other, requests and invitations
     * included (Twig: conversations_with_count(member), the "All conversations" link of the
     * side panel, to /conversations/with/{username}).
     */
    #[AsTwigFunction('conversations_with_count')]
    public function getConversationsWithCount(Member $other): int
    {
        $member = $this->security->getUser();
        if (!$member instanceof Member || $member === $other) {
            return 0;
        }

        return (int) new ConversationsWithAdapter($this->entityManager, $member, $other)->getNbResults();
    }

    /**
     * Variables for templates that show the folder list next to a conversation.
     *
     * @return array{folder: string, conversations: Pagerfanta, initiator: int, unreadOnly: bool, search: string}
     */
    public function getContext(Request $request, Member $member, string $defaultFolder = 'conversations'): array
    {
        $folder = $this->getFolder($request, $defaultFolder);
        $filters = $this->getFilters($request);

        return [
            'folder' => $folder,
            'conversations' => $this->getConversations($member, $folder, $filters['initiator'], $filters['unreadOnly'], $filters['search']),
        ] + $filters;
    }
}
