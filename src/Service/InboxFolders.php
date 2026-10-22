<?php

namespace App\Service;

use App\Entity\Member;
use App\Pagerfanta\ConversationsAdapter;
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

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
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
     * The list filters (?initiator=1|3, ?unread_only=1), as on the folder pages.
     *
     * @return array{initiator: int, unreadOnly: bool}
     */
    public function getFilters(Request $request): array
    {
        $initiator = (int) $request->query->get('initiator', '2');

        return [
            'initiator' => \in_array($initiator, [1, 2, 3], true) ? $initiator : 2,
            'unreadOnly' => '1' === $request->query->get('unread_only', '0'),
        ];
    }

    public function getConversations(Member $member, string $folder, int $initiator = 2, bool $unreadOnly = false): Pagerfanta
    {
        $adapter = match ($folder) {
            'messages' => MessagesAdapter::class,
            'requests' => RequestsAdapter::class,
            'invitations' => InvitationsAdapter::class,
            'spam' => SpamAdapter::class,
            'deleted' => DeletedAdapter::class,
            default => ConversationsAdapter::class,
        };

        $conversations = new Pagerfanta(new $adapter($this->entityManager, $member, $initiator, $unreadOnly));
        $conversations->setMaxPerPage($this->getItemsPerPage($member));
        $conversations->setCurrentPage(1);

        return $conversations;
    }

    /**
     * Unread conversations in the requests and invitations folders of the logged-in
     * member, for the folder menu (Twig: inbox_unread_counts()).
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
            'requests' => (new RequestsAdapter($this->entityManager, $member, 2, true))->getNbResults(),
            'invitations' => (new InvitationsAdapter($this->entityManager, $member, 2, true))->getNbResults(),
        ];
    }

    /**
     * Variables for templates that show the folder list next to a conversation.
     *
     * @return array{folder: string, conversations: Pagerfanta, initiator: int, unreadOnly: bool}
     */
    public function getContext(Request $request, Member $member, string $defaultFolder = 'conversations'): array
    {
        $folder = $this->getFolder($request, $defaultFolder);
        $filters = $this->getFilters($request);

        return [
            'folder' => $folder,
            'conversations' => $this->getConversations($member, $folder, $filters['initiator'], $filters['unreadOnly']),
        ] + $filters;
    }
}
