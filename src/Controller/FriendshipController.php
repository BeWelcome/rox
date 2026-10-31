<?php

namespace App\Controller;

use App\Doctrine\MemberStatusType;
use App\Entity\Friend;
use App\Entity\Member;
use App\Model\FriendshipModel;
use App\Repository\FriendRepository;
use App\Utilities\ChangeProfilePictureGlobals;
use App\Utilities\ItemsPerPageTraits;
use App\Utilities\MailLinkTrait;
use App\Utilities\ProfileSubmenu;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\SignedUriException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\IsSignatureValid;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;

class FriendshipController extends AbstractController
{
    use ItemsPerPageTraits;
    use MailLinkTrait;
    use TranslatedFlashTrait;
    use TranslatorTrait;

    /** The CSRF token id of the forms to ask for and to answer a friendship. */
    public const string TOKEN = 'friendship';

    private const array FLASHES = [
        FriendshipModel::ACCEPT => 'flash.friendship.confirmed',
        FriendshipModel::DECLINE => 'flash.friendship.dismissed',
        FriendshipModel::WITHDRAW => 'flash.friendship.withdrawn',
        FriendshipModel::REMOVE => 'flash.friendship.removed',
    ];

    public function __construct(
        // used by ItemsPerPageTraits
        private readonly EntityManagerInterface $entityManager,
        private readonly FriendRepository $friendRepository,
        private readonly FriendshipModel $friendshipModel,
        private readonly ProfileSubmenu $profileSubmenu,
        private readonly ChangeProfilePictureGlobals $globals,
    ) {
    }

    #[Route(path: '/members/{username:member}/friendship/add', name: 'add_friendship', methods: ['GET', 'POST'])]
    #[IsCsrfTokenValid(self::TOKEN, methods: ['POST'])]
    public function add(Request $request, Member $member): Response
    {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();
        $notAllowed = $this->notAllowed($loggedInMember, $member);
        if (null !== $notAllowed) {
            return $notAllowed;
        }

        if (null !== $this->friendRepository->findFriendshipBetween($loggedInMember, $member)) {
            return $this->redirectToRoute('edit_friendship', ['username' => $member->getUsername()]);
        }

        if ($request->isMethod('POST')) {
            $this->friendshipModel->request($loggedInMember, $member);
            $this->addTranslatedFlash('notice', 'flash.friendship.requested', ['username' => $member->getUsername()]);

            return $this->redirectToRoute('friendships', ['username' => $loggedInMember->getUsername()]);
        }

        return $this->render('friend/add.html.twig', [
            'member' => $member,
            'globals_js_json' => $this->globals->getGlobalsJsAsJson($loggedInMember, $member),
            'submenu' => $this->profileSubmenu->getSubmenu($loggedInMember, $member, ['active' => 'friends']),
        ]);
    }

    /**
     * The friendship with $member: accept or decline their request, withdraw one's own or remove the friendship.
     */
    #[Route(path: '/members/{username:member}/friendship/edit', name: 'edit_friendship', methods: ['GET', 'POST'])]
    #[IsCsrfTokenValid(self::TOKEN, methods: ['POST'])]
    public function edit(Request $request, Member $member): Response
    {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();
        $notAllowed = $this->notAllowed($loggedInMember, $member);
        if (null !== $notAllowed) {
            return $notAllowed;
        }

        $friend = $this->friendRepository->findFriendshipBetween($loggedInMember, $member);
        if (null === $friend) {
            return $this->redirectToRoute('add_friendship', ['username' => $member->getUsername()]);
        }

        if ($request->isMethod('POST')) {
            return $this->answer($friend, $loggedInMember, $request->request->getString('friendship'));
        }

        return $this->render('friend/edit.html.twig', [
            'member' => $member,
            'friend' => $friend,
            'actions' => $this->friendshipModel->getActions($friend, $loggedInMember),
            'globals_js_json' => $this->globals->getGlobalsJsAsJson($loggedInMember, $member),
            'submenu' => $this->profileSubmenu->getSubmenu($loggedInMember, $member, ['active' => 'friends']),
        ]);
    }

    /**
     * Link in the request mail: $left (the receiver) confirms the friendship $right asked for.
     */
    #[Route(path: '/members/{left}/friendship/{right}/confirm', name: 'confirm_friendship', methods: ['GET'])]
    #[IsSignatureValid]
    public function confirm(
        #[MapEntity(mapping: ['left' => 'username'])] Member $left,
        #[MapEntity(mapping: ['right' => 'username'])] Member $right,
        ?SignedUriException $invalidLink = null,
    ): Response {
        return $this->answerMailLink($left, $right, FriendshipModel::ACCEPT, $invalidLink);
    }

    /**
     * Link in the request mail: $left (the receiver) declines the friendship $right asked for.
     */
    #[Route(path: '/members/{left}/friendship/{right}/dismiss', name: 'dismiss_friendship', methods: ['GET'])]
    #[IsSignatureValid]
    public function dismiss(
        #[MapEntity(mapping: ['left' => 'username'])] Member $left,
        #[MapEntity(mapping: ['right' => 'username'])] Member $right,
        ?SignedUriException $invalidLink = null,
    ): Response {
        return $this->answerMailLink($left, $right, FriendshipModel::DECLINE, $invalidLink);
    }

    /**
     * The member's own friendships: the requests to answer, the ones they sent and their friends.
     */
    #[Route(path: '/members/{username:member}/friendships', name: 'friendships', methods: ['GET'])]
    public function friendships(Member $member): Response
    {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();
        if ($member !== $loggedInMember) {
            return $this->redirectToRoute('friends', ['username' => $member->getUsername()]);
        }

        return $this->render('friend/friendships.html.twig', [
            'member' => $member,
            'requests' => $this->friendRepository->findRequestsFor($member),
            'sent' => $this->friendRepository->findSentBy($member),
            'friends' => $this->friendRepository->findFriendsFor($member),
            'globals_js_json' => $this->globals->getGlobalsJsAsJson($loggedInMember, $member),
            'submenu' => $this->profileSubmenu->getSubmenu($loggedInMember, $member, ['active' => 'friends']),
        ]);
    }

    #[Route(path: '/members/{username:member}/friends/{page}', name: 'friends', requirements: ['page' => '\d+'])]
    public function friends(Member $member, int $page = 1): Response
    {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();

        return $this->render('friend/friends.html.twig', [
            'member' => $member,
            'friends' => $this->friendRepository->getFriends($member, $page, $this->getItemsPerPage($loggedInMember)),
            'globals_js_json' => $this->globals->getGlobalsJsAsJson($loggedInMember, $member),
            'submenu' => $this->profileSubmenu->getSubmenu($loggedInMember, $member, ['active' => 'friends']),
        ]);
    }

    /**
     * The old URLs of a member's relations: the list, adding one and removing one.
     */
    #[Route(path: '/members/{username}/relations', name: 'friends_old', methods: ['GET'], priority: 10)]
    #[Route(path: '/members/{username}/relations/add', name: 'add_friendship_old', methods: ['GET'], defaults: ['target' => 'add_friendship'])]
    #[Route(path: '/members/{username}/relations/add/finish', name: 'add_friendship_finish_old', methods: ['GET'], defaults: ['target' => 'add_friendship'])]
    #[Route(
        path: '/members/{username}/relations/delete/{relationId}/editprofile',
        name: 'remove_friendship_old',
        requirements: ['relationId' => '\d+'],
        methods: ['GET'],
        defaults: ['target' => 'edit_friendship'],
    )]
    public function redirectLegacyRelations(string $username, string $target = 'friends'): RedirectResponse
    {
        return $this->redirectToRoute($target, ['username' => $username], Response::HTTP_MOVED_PERMANENTLY);
    }

    private function answer(Friend $friend, Member $member, string $action): RedirectResponse
    {
        if ($this->friendshipModel->answer($friend, $member, $action)) {
            $this->addTranslatedFlash('notice', self::FLASHES[$action], [
                'username' => $friend->getOther($member)->getUsername(),
            ]);
        }

        return $this->redirectToRoute('friendships', ['username' => $member->getUsername()]);
    }

    private function answerMailLink(Member $receiver, Member $requester, string $action, ?SignedUriException $invalidLink): Response
    {
        if (null !== $invalidLink) {
            // the page answers the request too
            return $this->rejectMailLink('edit_friendship', ['username' => $requester->getUsername()]);
        }

        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();
        if ($receiver !== $loggedInMember) {
            return $this->redirectToRoute('members_profile', ['username' => $loggedInMember->getUsername()]);
        }

        $friend = $this->friendRepository->findFriendshipBetween($receiver, $requester);
        if (null === $friend || !$friend->isRequester($requester)) {
            // withdrawn or answered already
            return $this->redirectToRoute('friendships', ['username' => $receiver->getUsername()]);
        }

        return $this->answer($friend, $receiver, $action);
    }

    /**
     * A member can't be their own friend; new members can't ask for friendships before their profile is checked.
     */
    private function notAllowed(Member $loggedInMember, Member $member): ?RedirectResponse
    {
        if ($member === $loggedInMember) {
            return $this->redirectToRoute('members_profile', ['username' => $loggedInMember->getUsername()]);
        }

        if (MemberStatusType::ACCOUNT_ACTIVATED === $loggedInMember->getStatus()) {
            $this->addTranslatedFlash('notice', 'flash.friend.not.active');

            return $this->redirectToRoute('members_profile', ['username' => $member->getUsername()]);
        }

        return null;
    }
}
